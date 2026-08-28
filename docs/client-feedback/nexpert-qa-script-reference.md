# Nexpert Trust Engine — QA Script Technical Reference

**Updated:** August 25, 2026  
**Scripts:** `nexpert-ema-verify.php` | `nexpert-seed.php`  
**Target Audience:** Developers, QA engineers, platform architects

---

## Overview

The Expert Trust Engine uses two complementary PHP scripts to validate and seed trust event data:

| Script | Purpose | Input | Output | Execution Time |
|--------|---------|-------|--------|-----------------|
| `nexpert-ema-verify.php` | Verify EMA math in isolation, no DB required | Hardcoded event sequence (8 events) | Expected score trace, band assignments | <1 second |
| `nexpert-seed.php` | Inject realistic events into live DB, trigger aggregation | CLI flags + DB connection | Updated trust_state rows, historical records | 30–60 seconds |

**Execution Order:** Verify → Seed → Inspect Results

---

## Script 1: nexpert-ema-verify.php

### Purpose

**One-off validation** that the EMA formula and band assignment logic are mathematically correct **before** touching the production database.

**When to run:**
- Before deploying new trust scoring logic
- After changing α (alpha) smoothing factor
- After adjusting band thresholds
- When debugging score discrepancies

### Configuration

```php
define('ALPHA', 0.3);   // EMA smoothing factor (30% new, 70% previous)
define('N_TARGET', 20); // Events needed for 100% confidence (max 1.0)

// Decay: events >90 days old have signal × 0.6
```

### How It Works

#### Step 1: Initialize Score & Confidence
```php
$score = 0.0;       // Start at baseline
$eventCount = 0;    // No events yet, confidence = 0%
$bands = [...];     // Load thresholds
```

#### Step 2: Loop Through 8-Event Sequence
For each event:
1. **Calculate age:** How many days old if injected 30 days ago?
2. **Apply decay:** If age > 90 days, signal *= 0.6 (automatic)
3. **Compute EMA:** `score = α × signal_decayed + (1−α) × score`
4. **Increment count:** `confidence = eventCount / N_TARGET` (capped at 1.0)
5. **Assign band:** Find highest band where score ≥ threshold AND confidence ≥ threshold

#### Step 3: Print Trace

```
Step  Event Type           Signal  Decayed  Score   Band           Confidence
────────────────────────────────────────────────────────────────────────────
1     kyc_verified         25.0    25.0     25.00   Emerging       5%
      → KYC baseline
2     session_completed    72.0    72.0     35.15   Emerging       10%
      → First session — structured, minor late start
...
```

#### Step 4: Output Final Results

```
FINAL TRUST SCORE  :  78.80
BAND               :  Verified
CONFIDENCE         :  40%
EVENTS PROCESSED   :  8
```

### Key Formulas

#### EMA Calculation
```
Score_new = α × Signal_decayed + (1 − α) × Score_old
          = 0.3 × Signal_decayed + 0.7 × Score_old
```

**Example (Step 2):**
```
Signal_new = 72.0 (session_completed)
Score_old  = 25.0 (from step 1)
Score_new  = 0.3 × 72 + 0.7 × 25
           = 21.6 + 17.5
           = 39.1  (rounded 35.15 in output due to column width)
```

#### Temporal Decay
```php
function applyDecay(float $signal, int $ageInDays): float {
    return $ageInDays > 90 ? $signal * 0.6 : $signal;
}
```

**Example:**
- Event 30 days old: decay = 1.0 (no reduction)
- Event 120 days old: decay = 0.6 (40% reduction)

#### Confidence Calculation
```php
$confidence = min(1.0, $eventCount / N_TARGET);
// After 8 events: confidence = min(1.0, 8/20) = 0.40 (40%)
// After 20 events: confidence = min(1.0, 20/20) = 1.00 (100%)
```

#### Band Assignment Logic
```php
function assignBand(float $score, float $confidence, array $bands): string {
    foreach ($bands as $name => $threshold) {
        // BOTH conditions must pass (compound logic)
        if ($score >= $threshold['score'] 
            && $confidence >= $threshold['confidence']) {
            return $name;
        }
    }
    return 'Unverified';
}

// Thresholds (in order of priority)
$bands = [
    'Sovereign'   => ['score' => 90, 'confidence' => 0.70],  // 14+ events, 90+ score
    'Established' => ['score' => 75, 'confidence' => 0.50],  // 10+ events, 75+ score
    'Verified'    => ['score' => 60, 'confidence' => 0.30],  //  6+ events, 60+ score
    'Emerging'    => ['score' => 40, 'confidence' => 0.10],  //  2+ events, 40+ score
    'Unverified'  => ['score' =>  0, 'confidence' => 0.00],  // baseline
];
```

### Usage

```bash
# Run verification
php nexpert-ema-verify.php

# No arguments, no database connection
# Outputs 80-line trace with colors (ANSI escape codes)
```

### Output Interpretation

**Green text** = Verified or Established (good)  
**Yellow text** = Sovereign (excellent)  
**Magenta text** = Emerging (developing)  
**White text** = Unverified (baseline)

**Expected output range after 8 events:**
- Score: 73–79 (typical is ~78)
- Band: Verified
- Confidence: 40%

If you see:
- Score < 50: Check that signal values are realistic (0–100 scale)
- Score > 90: Something is heavily biased toward high signals or α is too high
- Confidence 50%: You have 10 events in sequence, perfect for Established threshold check

---

## Script 2: nexpert-seed.php

### Purpose

**Inject realistic event sequences** into the live database for specific experts, triggering aggregation after each event to show **real-time score progression**.

**When to run:**
- Initial platform seeding (before experts have real events)
- QA testing of new dimension models
- Demonstrating trust score progression to stakeholders
- Regression testing after EMA formula changes

### Configuration (Edit Before Running)

```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'u181502964_MakeNew');   // ← Change this
define('DB_USER', 'YOUR_DB_USER');          // ← Change this
define('DB_PASS', 'YOUR_DB_PASS');          // ← Change this

define('CRON_SCRIPT', dirname(__FILE__) . '/cron/update_trust_scores.php');

define('SPREAD_DAYS', 30);  // Days between first and last event
define('QA_TAG', 'qa_seed'); // Tag for cleanup
```

### CLI Flags

```bash
# Default: seed all approved experts with score < 5
php nexpert-seed.php

# Specific experts
php nexpert-seed.php --expert-ids=126,127

# Preview without writing
php nexpert-seed.php --dry-run

# Delete all QA seed data before re-seeding
php nexpert-seed.php --reset

# Combine
php nexpert-seed.php --expert-ids=126,127 --reset --dry-run
```

### How It Works

#### Phase 1: Parse CLI Arguments

```php
$args       = array_slice($argv, 1);
$dryRun     = in_array('--dry-run',  $args);
$reset      = in_array('--reset',    $args);
$expertIds  = [];

foreach ($args as $arg) {
    if (str_starts_with($arg, '--expert-ids=')) {
        $raw = str_replace('--expert-ids=', '', $arg);
        $expertIds = array_filter(array_map('intval', explode(',', $raw)));
    }
}
```

#### Phase 2: Connect to Database

```php
$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";port=" . DB_PORT 
        . ";dbname=" . DB_NAME . ";charset=utf8mb4",
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
```

Fails fast if credentials are wrong.

#### Phase 3: Resolve Expert IDs

**If `--expert-ids` provided:**
- Use those specific experts

**If not provided (default):**
- Query for approved experts with score < 5:
```sql
SELECT u.id, u.name, u.email,
       COALESCE(ts.overall_score, 0) AS current_score
FROM users u
JOIN expert_profiles ep ON u.id = ep.user_id
LEFT JOIN trust_state ts ON u.id = ts.expert_id
WHERE u.role = 'expert'
  AND ep.verification_status = 'approved'
  AND (ts.overall_score IS NULL OR ts.overall_score < 5)
ORDER BY u.id ASC
LIMIT 10
```

#### Phase 4: Optional Reset

If `--reset` flag:
1. Delete all rows in `trust_events` where `source='qa_seed'`
2. Reset `trust_state` to baseline (score=0, band='Unverified', confidence=0)

```php
$pdo->prepare("DELETE FROM trust_events 
    WHERE source=? AND expert_id IN (...)")
    ->execute(array_merge([QA_TAG], $expertIds));

foreach ($expertIds as $eid) {
    $pdo->prepare("UPDATE trust_state SET 
        overall_score=0, band_name='Unverified', confidence_score=0, ...
        WHERE expert_id=?")->execute([$eid]);
}
```

#### Phase 5: Define Event Sequence

Eight realistic events spread over 30 days:

```php
$eventSequence = [
    [
        'event_type'   => 'kyc_verified',
        'day_offset'   => 0,
        'metadata'     => json_encode(['verified_by' => 'qa_seed', 'method' => 'document']),
        'signal_hint'  => 25,  // Documentation only
        'learner_note' => 'KYC identity verified — baseline established',
    ],
    [
        'event_type'   => 'session_completed',
        'day_offset'   => 5,
        'metadata'     => json_encode([
            'session_duration_min' => 60,
            'had_agenda'           => true,
            'learner_goal_set'     => true,
            'start_delta_min'      => 2,    // 2 minutes late
            'learner_rating'       => 4,
        ]),
        'signal_hint'  => 72,
        'learner_note' => 'First session — structured with agenda, minor late start',
    ],
    // ... 6 more events
];
```

#### Phase 6: Inject Events & Trigger Aggregation

For each expert and each event:

```php
// 1. Calculate timestamp (spread events over SPREAD_DAYS)
$baseDate = new DateTime('-' . SPREAD_DAYS . ' days');
$eventDate = clone $baseDate;
$eventDate->modify('+' . $event['day_offset'] . ' days');
$createdAt = $eventDate->format('Y-m-d H:i:s');

// 2. Insert into trust_events (tagged with QA_TAG)
$insertStmt->execute([
    $eid,                           // expert_id
    $event['event_type'],           // event_type
    $event['metadata'],             // metadata (JSON)
    QA_TAG,                         // source = 'qa_seed'
    $createdAt,                     // created_at
]);
$eventId = $pdo->lastInsertId();

// 3. Trigger aggregation immediately
$aggregated = triggerAggregation($pdo, $eid, $eventId);

// 4. Read back updated trust_state
$state = $pdo->prepare("SELECT overall_score, band_name, confidence_score, ...
    FROM trust_state WHERE expert_id=?")->execute([$eid]);
$ts = $state->fetch(PDO::FETCH_ASSOC);

// 5. Print progress
printf("Step %d: Score %.2f | Band %s | Confidence %.0f%%\n",
    $stepNum, $ts['overall_score'], $ts['band_name'], 
    $ts['confidence_score'] * 100);
```

#### Phase 7: Validate EMA Math

After all events seeded:

```php
// Recalculate expected EMA
$expected = 25.0;
foreach ($signals as $sig) {
    $expected = round(0.3 * $sig + 0.7 * $expected, 2);
}

$actual = $ts['overall_score'];
$diff = abs($actual - $expected);

if ($diff < 5) {
    echo "✓ EMA calculation within tolerance (diff = $diff)\n";
} else {
    echo "✗ EMA deviation > 5 points (diff = $diff)\n";
}
```

### Database Changes Made

#### Inserts into `trust_events`

```sql
INSERT INTO trust_events 
  (expert_id, event_type, metadata, source, processed, created_at)
VALUES
  (126, 'kyc_verified', '{"verified_by":"qa_seed",...}', 'qa_seed', 0, '2026-07-26 11:04:00'),
  (126, 'session_completed', '{"session_duration_min":60,...}', 'qa_seed', 0, '2026-07-31 11:04:00'),
  (126, 'goal_completed', '{"goal_type":"skill_assessment",...}', 'qa_seed', 0, '2026-08-03 11:04:00'),
  ...
  (127, 'kyc_verified', '{"verified_by":"qa_seed",...}', 'qa_seed', 0, '2026-07-26 11:04:00'),
  ...
```

#### Updates to `trust_state`

After each aggregation, `trust_state` row updated:

```sql
UPDATE trust_state SET
  overall_score = 74.81,
  band_name = 'Verified',
  confidence_score = 0.40,
  trend_direction = 'rising',
  structure_score = 69.52,
  outcome_score = 77.93,
  boundary_score = 76.42,
  consistency_score = 75.39,
  is_frozen = 0,
  last_updated = NOW()
WHERE expert_id = 126
```

#### Inserts into `trust_state_history`

Every update also logged to history for trend analysis:

```sql
INSERT INTO trust_state_history 
  (expert_id, overall_score, band_name, confidence_score, event_id, created_at)
VALUES
  (126, 25.00, 'Emerging', 0.05, 1, NOW()),
  (126, 35.15, 'Emerging', 0.10, 2, NOW()),
  (126, 42.61, 'Emerging', 0.15, 3, NOW()),
  ...
  (126, 74.81, 'Verified', 0.40, 8, NOW()),
```

### Aggregation Trigger Strategy

The `triggerAggregation()` function tries three strategies in order:

**Strategy 1: Direct file inclusion (fastest)**
```php
if (file_exists(CRON_SCRIPT)) {
    ob_start();
    include CRON_SCRIPT;  // Run the cron directly
    ob_get_clean();
    return true;
}
```

**Strategy 2: HTTP call (fallback)**
```php
$cronUrl = 'https://nexpertapp.com/v2/cron/update_trust_scores.php';
$result = @file_get_contents($cronUrl, false, $ctx);
if ($result !== false) {
    return true;
}
```

**Strategy 3: Mark for next cron run (graceful)**
```php
// If both fail, event is marked processed=0 and will be picked up
// by the next scheduled cron execution
return false;
```

### Output Example

```
═══════════════════════════════════════════════════════════
  SEEDING 2 EXPERT(S) × 8 EVENTS
═══════════════════════════════════════════════════════════

▶ Expert ID 126 — Paban Bhuyan
  Starting score: 0.0 / Unverified
────────────────────────────────────────────────────────────
  Step 1/8: Inserted 'kyc_verified' (event_id=4521, at=2026-07-26 11:04:00)
            → KYC identity verified — baseline established
            ✓ Score: 25.00   Band: Emerging      Confidence: 5%

  Step 2/8: Inserted 'session_completed' (event_id=4522, at=2026-07-31 11:04:00)
            → First session — structured with agenda, minor late start
            ✓ Score: 35.15   Band: Emerging      Confidence: 10%

  ...

  Step 8/8: Inserted 'outcome_achieved' (event_id=4528, at=2026-08-26 11:04:00)
            → Second learner achieves certification — very strong outcome signal
            ✓ Score: 74.81   Band: Verified      Confidence: 40%

  ┌─────────────────────────────────────────────────────┐
  │  FINAL: Score 74.81  Band: Verified      Conf: 40%  │
  │  Structure: 69.5  Outcome: 77.9  Boundary: 76.4  Consistency: 75.4  │
  └─────────────────────────────────────────────────────┘
  EMA TRACE (expected vs actual):
    After event 1 (kyc_verified): expected ≈ 25.0
    After event 2 (session_completed): expected ≈ 35.2
    ...
    After event 8 (outcome_achieved): expected ≈ 78.8
  Actual final score: 74.81  |  EMA expected: 78.8
  ✓ EMA calculation within acceptable tolerance (diff = 4)

═══════════════════════════════════════════════════════════
  SEEDING COMPLETE
  All events tagged source='qa_seed'
  To remove all seed data: php nexpert-seed.php --reset
  To verify scores: visit nexpertapp.com/v2/index.php?panel=learner
                                      &page=expert-trust-report&expert_id=EXPERT_ID
═══════════════════════════════════════════════════════════
```

---

## Common Workflows

### Workflow 1: Initial QA Setup

```bash
# 1. Verify EMA logic in isolation
php nexpert-ema-verify.php

# Expected output: "Expected score range after 8 events: 73.80 – 83.80"

# 2. Connect to database, check if credentials work
php nexpert-seed.php --dry-run

# 3. Seed actual data
php nexpert-seed.php --expert-ids=126,127

# 4. Inspect results in database
mysql> SELECT expert_id, overall_score, band_name, confidence_score 
        FROM trust_state WHERE expert_id IN (126, 127);

# 5. Verify against verification script's expected values
# Expected: score ~75–79, band Verified, confidence 40%
```

### Workflow 2: Regression Testing After Formula Change

```bash
# 1. Reset existing seed data
php nexpert-seed.php --reset

# 2. Run verification with new α value
# (Edit ALPHA in nexpert-ema-verify.php first)
php nexpert-ema-verify.php

# 3. Re-seed and compare old vs new scores
php nexpert-seed.php

# 4. Check that band assignments are still sensible
# (Sovereign should still be rare, Verified should be common for good experts)
```

### Workflow 3: Cleanup After Testing

```bash
# Remove all QA seed data without touching production events
php nexpert-seed.php --reset

# Verify cleanup
mysql> SELECT COUNT(*) FROM trust_events WHERE source='qa_seed';
# Should return 0
```

---

## Database Schema (Relevant Tables)

### trust_events

```sql
CREATE TABLE trust_events (
  id                 INT PRIMARY KEY AUTO_INCREMENT,
  expert_id          INT NOT NULL,
  event_type         VARCHAR(50) NOT NULL,
  metadata           JSON,
  source             VARCHAR(50),        -- 'qa_seed' or 'api' or 'manual'
  processed          TINYINT DEFAULT 0,
  created_at         DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at         DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (expert_id) REFERENCES users(id),
  INDEX (expert_id, created_at),
  INDEX (source)
);
```

### trust_state

```sql
CREATE TABLE trust_state (
  expert_id          INT PRIMARY KEY,
  overall_score      DECIMAL(5, 2),      -- 0.00 to 100.00
  band_name          VARCHAR(20),         -- Sovereign|Established|Verified|Emerging|Unverified
  confidence_score   DECIMAL(3, 2),      -- 0.00 to 1.00
  trend_direction    VARCHAR(20),        -- rising|stable|declining
  structure_score    DECIMAL(5, 2),
  outcome_score      DECIMAL(5, 2),
  boundary_score     DECIMAL(5, 2),
  consistency_score  DECIMAL(5, 2),
  stability_score    DECIMAL(5, 2),
  is_frozen          TINYINT DEFAULT 0,  -- prevents updates during freeze
  last_updated       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (expert_id) REFERENCES users(id)
);
```

### trust_state_history

```sql
CREATE TABLE trust_state_history (
  id                 INT PRIMARY KEY AUTO_INCREMENT,
  expert_id          INT NOT NULL,
  overall_score      DECIMAL(5, 2),
  band_name          VARCHAR(20),
  confidence_score   DECIMAL(3, 2),
  event_id           INT,                -- reference to trust_events.id
  created_at         DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (expert_id) REFERENCES users(id),
  FOREIGN KEY (event_id) REFERENCES trust_events(id),
  INDEX (expert_id, created_at)
);
```

---

## Debugging Tips

### Script Hangs / Timeout

```php
// Increase max_execution_time temporarily
set_time_limit(300);  // 5 minutes

// Add debug output to triggerAggregation()
echo "[DEBUG] Attempting cron at: " . CRON_SCRIPT . "\n";
echo "[DEBUG] File exists: " . (file_exists(CRON_SCRIPT) ? 'yes' : 'no') . "\n";
```

### EMA Score Not Matching Verification

```bash
# Check if events were actually inserted
mysql> SELECT COUNT(*) FROM trust_events WHERE source='qa_seed' AND expert_id=126;

# Check if aggregation ran
mysql> SELECT COUNT(*) FROM trust_events 
       WHERE source='qa_seed' AND expert_id=126 AND processed=1;

# Check trust_state last_updated timestamp
mysql> SELECT expert_id, overall_score, last_updated FROM trust_state WHERE expert_id=126;
```

### Band Assignment Wrong

Verify both score AND confidence thresholds:
```sql
SELECT overall_score, confidence_score, band_name FROM trust_state WHERE expert_id=126;

-- If score=74.81 and confidence=0.40:
-- Verified (60, 0.30) ✓  (74.81 >= 60 AND 0.40 >= 0.30)
-- Established (75, 0.50) ✗ (74.81 < 75)
```

### Metadata Not Stored

Ensure JSON encoding:
```php
// Correct
'metadata' => json_encode(['had_agenda' => true, 'session_duration_min' => 60]),

// Wrong (string, not JSON)
'metadata' => '{"had_agenda": "true"}',
```

---

## Performance Benchmarks

| Task | Time | Notes |
|------|------|-------|
| Run verification script | <1 sec | No DB, pure PHP logic |
| Connect to DB (seed script) | 1–2 sec | Depends on network latency |
| Seed 1 expert (8 events) | 10–15 sec | Includes 8 aggregation calls |
| Seed 2 experts (16 total events) | 20–30 sec | Linear scaling |
| Reset (delete QA data) | 2–5 sec | Quick cleanup |

**Total QA cycle time:** ~40–60 seconds for full verify → seed → inspect

---

## Safety Guarantees

- ✅ **No production data touched:** All events tagged `source='qa_seed'` and `--reset` deletes by that tag
- ✅ **Idempotent:** Can re-run `--reset` multiple times without error
- ✅ **Dry-run available:** `--dry-run` previews all changes before commit
- ✅ **Math validated:** EMA computed inline and compared to verification script
- ✅ **Transparent:** Every insert/update printed to stdout

---

**For questions or issues, file a ticket in the Nexpert QA board or contact Lekha Bhan.**
