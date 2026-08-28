<?php
/**
 * Trust Aggregator Engine — MVP2
 * Computes Trust Scores using EMA with 5-band naming,
 * real stability, confidence, trend direction, and signal decay.
 *
 * WHAT CHANGED FROM ORIGINAL:
 * - 5-band mapping: Sovereign/Established/Verified/Emerging/Unverified
 * - Real stability_score from history variance (was hardcoded 100)
 * - Real confidence_score from signal data completeness
 * - Trend direction: rising/stable/declining from history delta
 * - Signal decay: signals >90 days old weighted at 60%
 * - trigger_event_id written to trust_state_history
 * - Trust change notifications queued automatically
 *
 * WHAT IS UNCHANGED:
 * - EMA formula: new = (0.3 * signal) + (0.7 * previous)
 * - Alpha value: 0.3
 * - All DB reads/writes to trust_state and trust_state_history
 * - aggregateAll() public interface
 */

class TrustAggregator {
    private $pdo;
    private $alpha          = 0.3;
    private $decayDays      = 90;   // signals older than this are decayed
    private $decayWeight    = 0.6;  // old signals weighted at 60%
    private $expectedSignals = 20;  // target signals for 100% confidence

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ─────────────────────────────────────────────
    // PUBLIC: aggregate all experts
    // ─────────────────────────────────────────────
    public function aggregateAll() {
        $results = [];
        $experts = $this->pdo->query("SELECT id FROM users WHERE role = 'expert'")->fetchAll();

        foreach ($experts as $expert) {
            $result = $this->aggregateOne($expert['id']);
            $results[] = $result;
        }
        return $results;
    }

    // ─────────────────────────────────────────────
    // PUBLIC: aggregate single expert (called by event processor too)
    // ─────────────────────────────────────────────
    public function aggregateOne($expertId, $triggerEventId = null) {
        // Ensure trust_state row exists
        $this->pdo->prepare("
            INSERT INTO trust_state (expert_id, overall_score, trust_tier, band_name, last_updated)
            VALUES (?, 0, 'C', 'Unverified', NOW())
            ON DUPLICATE KEY UPDATE expert_id = expert_id
        ")->execute([$expertId]);

        // Fetch current state
        $stmt = $this->pdo->prepare("SELECT * FROM trust_state WHERE expert_id = ?");
        $stmt->execute([$expertId]);
        $state = $stmt->fetch(PDO::FETCH_ASSOC);

        // Skip if frozen
        if (!empty($state['is_frozen'])) {
            return ['expert_id' => $expertId, 'status' => 'frozen'];
        }

        // Fetch all signals
        $stmt = $this->pdo->prepare("SELECT * FROM trust_signals WHERE expert_id = ? ORDER BY created_at ASC");
        $stmt->execute([$expertId]);
        $signals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($signals)) {
            return ['expert_id' => $expertId, 'status' => 'no_signals'];
        }

        // ── EMA with decay ──────────────────────
        $scores = [
            'structure'   => (float)($state['structure_score']   ?? 0),
            'outcome'     => (float)($state['outcome_score']      ?? 0),
            'boundary'    => (float)($state['boundary_score']     ?? 0),
            'consistency' => (float)($state['consistency_score']  ?? 0),
        ];

        $now = new DateTime();
        foreach ($signals as $signal) {
            $type  = $signal['agent_type'];
            $value = (float)$signal['signal_value'];

            // Apply decay for old signals
            $signalDate = new DateTime($signal['created_at']);
            $daysOld    = (int)$now->diff($signalDate)->days;
            if ($daysOld > $this->decayDays) {
                $value = $value * $this->decayWeight;
            }

            if (isset($scores[$type])) {
                $scores[$type] = ($this->alpha * $value) + ((1 - $this->alpha) * $scores[$type]);
            }
        }

        // ── Overall score ───────────────────────
        $oldScore     = (float)($state['overall_score'] ?? 0);
        $overallScore = round(
            ($scores['structure'] + $scores['outcome'] + $scores['boundary'] + $scores['consistency']) / 4,
            2
        );

        // ── 5-band mapping ──────────────────────
        $bandName = $this->getBandName($overallScore);

        // Legacy 3-tier (keep for backward compat with any code reading trust_tier)
        $tier = 'C';
        if ($overallScore >= 75) $tier = 'A';
        elseif ($overallScore >= 50) $tier = 'B';

        // ── Stability (variance of last 10 history scores) ──
        $stabilityScore = $this->computeStability($expertId);

        // ── Confidence (signal count vs expected) ───────────
        $confidenceScore = min(100, round((count($signals) / $this->expectedSignals) * 100, 2));

        // ── Trend (last 3 vs previous 3 from history) ───────
        $trendDirection = $this->computeTrend($expertId);

        // ── Update trust_state ──────────────────
        $stmt = $this->pdo->prepare("
            UPDATE trust_state SET
                overall_score    = ?,
                trust_tier       = ?,
                band_name        = ?,
                stability_score  = ?,
                confidence_score = ?,
                trend_direction  = ?,
                structure_score  = ?,
                outcome_score    = ?,
                boundary_score   = ?,
                consistency_score = ?,
                last_updated     = NOW()
            WHERE expert_id = ?
        ");
        $stmt->execute([
            $overallScore, $tier, $bandName,
            $stabilityScore, $confidenceScore, $trendDirection,
            $scores['structure'], $scores['outcome'],
            $scores['boundary'],  $scores['consistency'],
            $expertId
        ]);

        // ── Archive to history ──────────────────
        $this->pdo->prepare("
            INSERT INTO trust_state_history
                (expert_id, overall_score, trust_tier, band_name, stability_score, confidence_score, trigger_event_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ")->execute([
            $expertId, $overallScore, $tier, $bandName,
            $stabilityScore, $confidenceScore, $triggerEventId
        ]);

        // ── Queue notification if score changed significantly ──
        $delta = round($overallScore - $oldScore, 2);
        if (abs($delta) >= 1.0) {
            $this->queueNotification(
                $expertId,
                $oldScore,
                $overallScore,
                $delta,
                $state['band_name'] ?? 'Unverified',
                $bandName,
                $triggerEventId
            );
        }

        return [
            'expert_id'   => $expertId,
            'score'       => $overallScore,
            'tier'        => $tier,
            'band'        => $bandName,
            'confidence'  => $confidenceScore,
            'trend'       => $trendDirection,
            'stability'   => $stabilityScore,
        ];
    }

    // ─────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────

    private function getBandName(float $score): string {
        if ($score >= 90) return 'Sovereign';
        if ($score >= 75) return 'Established';
        if ($score >= 60) return 'Verified';
        if ($score >= 40) return 'Emerging';
        return 'Unverified';
    }

    private function computeStability(int $expertId): float {
        $stmt = $this->pdo->prepare("
            SELECT overall_score
            FROM trust_state_history
            WHERE expert_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$expertId]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($rows) < 2) return 100.0; // not enough history yet

        $mean = array_sum($rows) / count($rows);
        $variance = 0;
        foreach ($rows as $v) {
            $variance += pow($v - $mean, 2);
        }
        $variance /= count($rows);
        $stdDev = sqrt($variance);

        // Convert to 0-100 stability score: lower stdDev = higher stability
        return round(max(0, 100 - ($stdDev * 4)), 2);
    }

    private function computeTrend(int $expertId): string {
        $stmt = $this->pdo->prepare("
            SELECT overall_score
            FROM trust_state_history
            WHERE expert_id = ?
            ORDER BY created_at DESC
            LIMIT 6
        ");
        $stmt->execute([$expertId]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($rows) < 6) return 'stable';

        $recent = array_slice($rows, 0, 3);
        $older  = array_slice($rows, 3, 3);

        $avgRecent = array_sum($recent) / 3;
        $avgOlder  = array_sum($older)  / 3;
        $delta     = $avgRecent - $avgOlder;

        if ($delta > 2)  return 'rising';
        if ($delta < -2) return 'declining';
        return 'stable';
    }

    private function queueNotification(
        int    $expertId,
        float  $scoreOld,
        float  $scoreNew,
        float  $delta,
        string $bandOld,
        string $bandNew,
        ?int   $triggerEventId
    ): void {
        // Get event type for explanation
        $eventType = null;
        if ($triggerEventId) {
            $stmt = $this->pdo->prepare("SELECT event_type FROM trust_events WHERE id = ?");
            $stmt->execute([$triggerEventId]);
            $eventType = $stmt->fetchColumn() ?: null;
        }

        $explanation = $this->buildExplanation($delta, $bandOld, $bandNew, $eventType);

        $this->pdo->prepare("
            INSERT INTO trust_notifications
                (expert_id, score_old, score_new, delta, band_old, band_new, event_type, explanation_text, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ")->execute([
            $expertId, $scoreOld, $scoreNew, $delta,
            $bandOld, $bandNew, $eventType, $explanation
        ]);
    }

    private function buildExplanation(float $delta, string $bandOld, string $bandNew, ?string $eventType): string {
        $direction = $delta > 0 ? 'increased by' : 'decreased by';
        $points    = abs($delta);
        $event     = $eventType ? str_replace('_', ' ', $eventType) : 'a recent interaction';

        $text = "Your Trust Score {$direction} {$points} points following {$event}.";

        if ($bandOld !== $bandNew) {
            $text .= " You moved from {$bandOld} to {$bandNew}.";
        }

        $actionMap = [
            'session_completed'      => 'Completing sessions consistently is your strongest trust signal. Keep going.',
            'outcome_achieved'       => 'A verified learner outcome is the most powerful signal in the system. Well done.',
            'complaint_logged'       => 'A complaint was logged. Address it directly with the learner to begin recovery.',
            'session_no_show'        => 'A missed session significantly impacts your Reliability signal. Avoid cancellations.',
            'feedback_submitted'     => 'Learner feedback updated your Outcome and Consistency signals.',
            'booking_created'        => 'A new booking updated your Consistency signal.',
            'repeat_booking'         => 'A repeat learner is one of the strongest signals of genuine value delivery.',
            'kyc_verified'           => 'Identity verification strengthened your credibility foundation.',
        ];

        $text .= ' ' . ($actionMap[$eventType] ?? 'Continue delivering quality sessions to build your score.');
        return $text;
    }
}
