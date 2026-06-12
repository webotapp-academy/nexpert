<?php
/**
 * Trust Aggregator Engine
 * Logic for calculating trust scores and tiers
 */

class TrustAggregator {
    private $pdo;
    private $alpha = 0.3;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function aggregateAll() {
        $results = [];
        
        // 1. Get all experts
        $experts = $this->pdo->query("SELECT id FROM users WHERE role = 'expert'")->fetchAll();

        foreach ($experts as $expert) {
            $expertId = $expert['id'];
            
            // 2. Ensure trust_state exists
            $this->pdo->prepare("
                INSERT INTO trust_state (expert_id, overall_score, trust_tier, last_updated)
                VALUES (?, 0, 'C', NOW())
                ON DUPLICATE KEY UPDATE expert_id = expert_id
            ")->execute([$expertId]);

            // 3. Fetch current state
            $stmt = $this->pdo->prepare("SELECT * FROM trust_state WHERE expert_id = ?");
            $stmt->execute([$expertId]);
            $state = $stmt->fetch();

            // Skip if frozen
            if ($state['is_frozen'] == 1) {
                $results[] = ['expert_id' => $expertId, 'status' => 'frozen'];
                continue;
            }

            // 4. Fetch signals
            $stmt = $this->pdo->prepare("SELECT * FROM trust_signals WHERE expert_id = ? ORDER BY created_at ASC");
            $stmt->execute([$expertId]);
            $signals = $stmt->fetchAll();

            if (empty($signals)) {
                $results[] = ['expert_id' => $expertId, 'status' => 'no_signals'];
                continue;
            }

            $scores = [
                'structure' => (float)$state['structure_score'],
                'outcome' => (float)$state['outcome_score'],
                'boundary' => (float)$state['boundary_score'],
                'consistency' => (float)$state['consistency_score']
            ];

            foreach ($signals as $signal) {
                $type = $signal['agent_type'];
                $value = (float)$signal['signal_value'];
                
                if (isset($scores[$type])) {
                    $scores[$type] = ($this->alpha * $value) + ((1 - $this->alpha) * $scores[$type]);
                }
            }

            // 5. Calculate Overall Score & Stability
            $overallScore = ($scores['structure'] + $scores['outcome'] + $scores['boundary'] + $scores['consistency']) / 4;
            $stabilityScore = 100; 

            // 6. Map to Tiers
            $tier = 'C';
            if ($overallScore >= 80) $tier = 'A';
            elseif ($overallScore >= 50) $tier = 'B';

            // 7. Update Trust State
            $stmt = $this->pdo->prepare("
                UPDATE trust_state SET
                    overall_score = ?,
                    trust_tier = ?,
                    stability_score = ?,
                    structure_score = ?,
                    outcome_score = ?,
                    boundary_score = ?,
                    consistency_score = ?,
                    last_updated = NOW()
                WHERE expert_id = ?
            ");
            $stmt->execute([
                $overallScore, $tier, $stabilityScore, 
                $scores['structure'], $scores['outcome'], 
                $scores['boundary'], $scores['consistency'],
                $expertId
            ]);

            // 8. Archive to History
            $this->pdo->prepare("
                INSERT INTO trust_state_history (expert_id, overall_score, trust_tier, stability_score, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ")->execute([$expertId, $overallScore, $tier, $stabilityScore]);

            $results[] = ['expert_id' => $expertId, 'score' => $overallScore, 'tier' => $tier];
        }
        
        return $results;
    }
}
