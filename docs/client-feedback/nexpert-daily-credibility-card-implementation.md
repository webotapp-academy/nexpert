# Daily Credibility Update Card — Implementation Guide

**Date:** August 25, 2026  
**Feature:** Shareable LinkedIn Card Generation  
**Status:** Design + Implementation Spec  
**Author:** Lekha Bhan, Nexpert AI Architecture

---

## Overview

The **Daily Credibility Update Card** is a dynamic, event-triggered LinkedIn card that visualizes an expert's trust score growth and credibility achievements. Unlike static badges, the card is generated **only when something meaningful changes**, making it genuinely worth sharing.

**Key Principle:** Don't ask experts to share their score every day. Make the system produce meaningful achievements worth sharing.

---

## Architecture: When Cards Are Generated

### Trigger Events (Not Daily, Only on Achievement)

The card system monitors `trust_state_history` and generates a shareable card only when one of these events occurs:

| Trigger | Condition | Card Message | Example |
|---------|-----------|--------------|---------|
| **Milestone Crossed** | `overall_score` crosses 800, 850, 900 | 🎯 You crossed X Credibility Points | 800, 850, 900, etc. |
| **Session Count** | Reaches 25th, 50th, 100th verified session | ⭐ Your Xth verified expert session | "Your 50th verified session" |
| **Ranking Movement** | `expert_rank` improves by ≥10 positions this week | 📈 Moved from #42 → #31 this week | Rank jumps |
| **Expertise Recognition** | New expertise tag enters top percentile | 🧠 You're now top 10% in [Topic] | "fastest-rising RAG experts" |
| **Learner Outcome Rate** | `learner_satisfaction` ≥ 92% OR 90+ certified learners | 💬 92% of learners rated your sessions highly | "10 learners certified" |
| **Credibility Growth** | `overall_score` gains ≥50 points in 90 days | 🚀 +64 credibility points in 90 days | "+64 in Q3" |
| **Band Promotion** | Jumps from Emerging→Verified or Verified→Established | 🏅 You've earned Established status | "Band: Verified" |
| **Top Performer** | Enters top 10%, 5%, 1% of experts on topic | 👑 Top 5% of AI Experts | "Top 5%" |

### No Trigger = No Card

If the expert's trust score stayed at 847 yesterday and is 847 today, **no card is generated**. This prevents notification fatigue and maintains the value of each card when it appears.

---

## Database Schema

### New Tables

#### `credibility_card_events`

Tracks when cards should be generated.

```sql
CREATE TABLE credibility_card_events (
  id                    INT PRIMARY KEY AUTO_INCREMENT,
  expert_id             INT NOT NULL,
  trigger_type          VARCHAR(50) NOT NULL,  -- 'milestone', 'session_count', 'ranking_jump', etc.
  trigger_condition     JSON,                  -- { "threshold": 800, "previous_score": 795 }
  card_data             JSON,                  -- Rendered card content (see below)
  score_before          DECIMAL(5, 2),
  score_after           DECIMAL(5, 2),
  point_gain            DECIMAL(5, 2),
  generated_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
  shared_to_linkedin    TINYINT DEFAULT 0,
  shared_at             DATETIME,
  share_url             VARCHAR(255),          -- LinkedIn post URL if shared
  impressions           INT DEFAULT 0,         -- Track if LinkedIn API provides this
  engagement_rate       DECIMAL(5, 3),        -- Engagement / impressions
  
  FOREIGN KEY (expert_id) REFERENCES users(id),
  INDEX (expert_id, generated_at),
  INDEX (trigger_type, generated_at)
);
```

#### `credibility_card_templates`

Predefined templates for each trigger type (for A/B testing).

```sql
CREATE TABLE credibility_card_templates (
  id                    INT PRIMARY KEY AUTO_INCREMENT,
  trigger_type          VARCHAR(50) NOT NULL,
  variant               VARCHAR(20),           -- 'a', 'b', 'control'
  title_template        VARCHAR(255),          -- e.g., "🎯 You crossed {score} Credibility Points"
  subtitle_template     VARCHAR(255),
  body_json             JSON,                  -- Layout, styling, metrics
  cta_text              VARCHAR(100),          -- "View my verified profile"
  is_active             TINYINT DEFAULT 1,
  created_at            DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Example row:
INSERT INTO credibility_card_templates 
  (trigger_type, variant, title_template, body_json)
VALUES
  ('milestone_crossed', 'a', '🎯 You crossed {score} Credibility Points', 
   JSON_OBJECT(
     'before_score', '{score_before}',
     'after_score', '{score_after}',
     'point_gain', '{point_gain}',
     'achievement', 'Milestone reached'
   ));
```

---

## Card Generation Engine

### Logic Flow

```
Daily Cron (12 AM UTC) → Run Card Generation
  │
  ├─ Fetch all experts with score changes in last 24h
  ├─ For each expert:
  │   ├─ Check last_trust_state_history record
  │   ├─ Compare to previous day's record
  │   ├─ Evaluate against 8 trigger types
  │   ├─ If any trigger matches:
  │   │   ├─ Load template for that trigger
  │   │   ├─ Render card JSON
  │   │   ├─ Insert into credibility_card_events
  │   │   └─ Queue for expert notification
  │   └─ If no trigger, do nothing
  │
  └─ End of cron
```

### PHP Implementation

```php
<?php
/**
 * Card Generation Cron: cron/generate_credibility_cards.php
 * Run daily at 12 AM UTC via: php cron/generate_credibility_cards.php
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'u181502964_MakeNew');
define('DB_USER', 'YOUR_DB_USER');
define('DB_PASS', 'YOUR_DB_PASS');

$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
    DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Fetch all experts with trust_state changes in last 24 hours
$stmt = $pdo->query("
    SELECT DISTINCT h1.expert_id
    FROM trust_state_history h1
    WHERE h1.created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
      AND h1.expert_id NOT IN (
        SELECT expert_id FROM credibility_card_events 
        WHERE generated_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
      )
    ORDER BY h1.expert_id
");

$expertIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($expertIds as $expertId) {
    generateCardIfEligible($pdo, $expertId);
}

/**
 * Evaluate expert against all trigger types
 */
function generateCardIfEligible(PDO $pdo, int $expertId): void {
    // Get today's and yesterday's trust state
    $today = getTrustStateAsOf($pdo, $expertId, 'today');
    $yesterday = getTrustStateAsOf($pdo, $expertId, 'yesterday');
    
    if (!$today || !$yesterday) return;
    
    $scoreGain = $today['overall_score'] - $yesterday['overall_score'];
    
    // Check Trigger 1: Milestone Crossed
    $milestones = [800, 850, 900];
    foreach ($milestones as $milestone) {
        if ($yesterday['overall_score'] < $milestone && $today['overall_score'] >= $milestone) {
            createCard($pdo, $expertId, 'milestone_crossed', [
                'score_before' => $yesterday['overall_score'],
                'score_after' => $today['overall_score'],
                'milestone' => $milestone,
                'point_gain' => $scoreGain
            ]);
            return; // Only one card per day
        }
    }
    
    // Check Trigger 2: Session Count Milestone
    $todaySessionCount = countVerifiedSessions($pdo, $expertId);
    $yesterdaySessionCount = countVerifiedSessions($pdo, $expertId, 'yesterday');
    $sessionMilestones = [25, 50, 100, 250];
    
    foreach ($sessionMilestones as $milestone) {
        if ($yesterdaySessionCount < $milestone && $todaySessionCount >= $milestone) {
            createCard($pdo, $expertId, 'session_count', [
                'session_count' => $todaySessionCount,
                'milestone' => $milestone
            ]);
            return;
        }
    }
    
    // Check Trigger 3: Ranking Jump
    $todayRank = getRankForTopic($pdo, $expertId);
    $yesterdayRank = getRankForTopic($pdo, $expertId, 'yesterday');
    $rankImprovement = $yesterdayRank - $todayRank; // Lower is better
    
    if ($rankImprovement >= 10) {
        createCard($pdo, $expertId, 'ranking_jump', [
            'rank_before' => $yesterdayRank,
            'rank_after' => $todayRank,
            'improvement' => $rankImprovement,
            'period' => 'this week'
        ]);
        return;
    }
    
    // Check Trigger 4: Expertise Recognition
    $todayTopics = getTopicsInPercentile($pdo, $expertId, 10); // Top 10%
    $yesterdayTopics = getTopicsInPercentile($pdo, $expertId, 10, 'yesterday');
    $newTopics = array_diff($todayTopics, $yesterdayTopics);
    
    if (!empty($newTopics)) {
        createCard($pdo, $expertId, 'expertise_recognition', [
            'topics' => $newTopics,
            'percentile' => 10
        ]);
        return;
    }
    
    // Check Trigger 5: Learner Outcome Rate
    $learnerSatisfaction = getLearnerSatisfactionRate($pdo, $expertId);
    $certifiedCount = countCertifiedLearners($pdo, $expertId);
    
    if ($learnerSatisfaction >= 0.92 || $certifiedCount >= 10) {
        createCard($pdo, $expertId, 'learner_outcome', [
            'satisfaction_rate' => $learnerSatisfaction * 100,
            'certified_learners' => $certifiedCount
        ]);
        return;
    }
    
    // Check Trigger 6: Credibility Growth (90-day)
    $score90DaysAgo = getTrustStateAsOf($pdo, $expertId, '90 days ago')['overall_score'] ?? 0;
    $growth90Days = $today['overall_score'] - $score90DaysAgo;
    
    if ($growth90Days >= 50) {
        createCard($pdo, $expertId, 'credibility_growth', [
            'growth_amount' => $growth90Days,
            'period_days' => 90,
            'score_before_90d' => $score90DaysAgo,
            'score_after_90d' => $today['overall_score']
        ]);
        return;
    }
    
    // Check Trigger 7: Band Promotion
    if ($yesterday['band_name'] !== $today['band_name']) {
        $bandHierarchy = ['Unverified', 'Emerging', 'Verified', 'Established', 'Sovereign'];
        $yRank = array_search($yesterday['band_name'], $bandHierarchy);
        $tRank = array_search($today['band_name'], $bandHierarchy);
        
        if ($tRank > $yRank) {
            createCard($pdo, $expertId, 'band_promotion', [
                'from_band' => $yesterday['band_name'],
                'to_band' => $today['band_name']
            ]);
            return;
        }
    }
    
    // Check Trigger 8: Top Performer
    $percentile = getPercentile($pdo, $expertId);
    $yesterdayPercentile = getPercentile($pdo, $expertId, 'yesterday');
    
    foreach ([1, 5, 10] as $threshold) {
        if ($yesterdayPercentile > $threshold && $percentile <= $threshold) {
            createCard($pdo, $expertId, 'top_performer', [
                'percentile' => $threshold,
                'total_experts' => getTotalExpertCount($pdo)
            ]);
            return;
        }
    }
}

/**
 * Render and insert card
 */
function createCard(PDO $pdo, int $expertId, string $triggerType, array $data): void {
    // Load template
    $tmpl = $pdo->prepare("
        SELECT * FROM credibility_card_templates
        WHERE trigger_type = ? AND is_active = 1
        ORDER BY variant = 'a' DESC
        LIMIT 1
    ")->execute([$triggerType]);
    
    $template = $tmpl->fetch(PDO::FETCH_ASSOC);
    if (!$template) return;
    
    // Render card JSON
    $cardData = renderCard($pdo, $expertId, $template, $data);
    
    // Insert card event
    $insert = $pdo->prepare("
        INSERT INTO credibility_card_events
          (expert_id, trigger_type, trigger_condition, card_data, 
           score_before, score_after, point_gain, generated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $insert->execute([
        $expertId,
        $triggerType,
        json_encode($data),
        json_encode($cardData),
        $data['score_before'] ?? null,
        $data['score_after'] ?? null,
        ($data['score_after'] ?? 0) - ($data['score_before'] ?? 0)
    ]);
    
    // Queue notification (optional)
    notifyExpertNewCard($expertId);
}

/**
 * Render card HTML/JSON for display
 */
function renderCard(PDO $pdo, int $expertId, array $template, array $data): array {
    $expert = getExpertProfile($pdo, $expertId);
    $trustState = getTrustStateAsOf($pdo, $expertId, 'today');
    
    return [
        'header' => [
            'logo' => 'nexpert-logo.png',
            'title' => interpolate($template['title_template'], $data),
            'subtitle' => 'Daily Credibility Update',
            'verification_badge' => 'AI-VERIFIED EXPERT'
        ],
        'profile' => [
            'photo_url' => $expert['profile_photo_url'],
            'name' => $expert['name'],
            'title' => $expert['expert_title'],
            'credentials' => $expert['credentials']
        ],
        'metrics' => [
            'score_before' => $data['score_before'] ?? $trustState['overall_score'],
            'score_after' => $data['score_after'] ?? $trustState['overall_score'],
            'point_gain' => ($data['score_after'] ?? 0) - ($data['score_before'] ?? 0),
            'trend_direction' => ($data['score_after'] ?? 0) > ($data['score_before'] ?? 0) ? 'up' : 'stable'
        ],
        'achievements' => extractAchievementsForCard($data),
        'expertise_tags' => array_slice($expert['expertise_topics'], 0, 3),
        'ranking' => [
            'percentile' => getPercentile($pdo, $expertId),
            'label' => "Top " . getPercentile($pdo, $expertId) . "% of Experts on " . ($expert['primary_topic'] ?? 'Nexpert')
        ],
        'cta' => [
            'text' => $template['cta_text'] ?? 'View my verified profile',
            'url' => "nexpertapp.com/expert/" . $expertId,
            'profile_qr' => generateQRCode("nexpertapp.com/expert/" . $expertId)
        ],
        'footer' => [
            'tagline' => 'Where expertise becomes measurable.',
            'share_text' => generateLinkedInShareText($expertId, $data)
        ]
    ];
}

function interpolate(string $template, array $data): string {
    foreach ($data as $key => $value) {
        $template = str_replace('{' . $key . '}', $value, $template);
    }
    return $template;
}

function extractAchievementsForCard(array $data): array {
    $achievements = [];
    
    if (isset($data['certified_learners']) && $data['certified_learners'] >= 10) {
        $achievements[] = ['icon' => '👨‍🎓', 'text' => $data['certified_learners'] . ' learners certified'];
    }
    if (isset($data['satisfaction_rate']) && $data['satisfaction_rate'] >= 92) {
        $achievements[] = ['icon' => '⭐', 'text' => $data['satisfaction_rate'] . '% learner satisfaction'];
    }
    if (isset($data['session_count'])) {
        $achievements[] = ['icon' => '✓', 'text' => $data['session_count'] . ' verified sessions'];
    }
    
    return $achievements;
}

?>
```

---

## API Endpoints

### 1. Get Available Cards

**Endpoint:** `GET /api/v2/experts/:expert_id/credibility-cards`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1024,
      "trigger_type": "milestone_crossed",
      "title": "🎯 You crossed 850 Credibility Points",
      "card_data": { ... },
      "generated_at": "2026-08-25T12:00:00Z",
      "shared_to_linkedin": false,
      "impressions": 0
    },
    {
      "id": 1023,
      "trigger_type": "session_count",
      "title": "⭐ Your 50th verified expert session",
      "card_data": { ... },
      "generated_at": "2026-08-24T12:00:00Z",
      "shared_to_linkedin": true,
      "share_url": "https://www.linkedin.com/posts/activity-1234567890",
      "impressions": 342,
      "engagement_rate": 0.087
    }
  ]
}
```

### 2. Generate Card on Demand

**Endpoint:** `POST /api/v2/experts/:expert_id/credibility-cards/generate`

**Request:**
```json
{
  "force": true
}
```

**Response:** Same as card object above.

### 3. Share Card to LinkedIn

**Endpoint:** `POST /api/v2/credibility-cards/:card_id/share-linkedin`

**Request:**
```json
{
  "include_text": true,
  "text_override": "Optional: custom share text"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "card_id": 1024,
    "share_url": "https://www.linkedin.com/posts/activity-1234567890",
    "shared_at": "2026-08-25T14:32:10Z"
  }
}
```

---

## Frontend: React Card Component

```jsx
// components/CredibilityCard.jsx

import React, { useState } from 'react';
import { Share2, ArrowUp } from 'lucide-react';

export const CredibilityCard = ({ 
  card, 
  expertId, 
  onShare 
}) => {
  const [sharing, setSharing] = useState(false);
  const cardData = card.card_data;
  
  const handleShare = async () => {
    setSharing(true);
    try {
      await onShare(card.id);
      // Show success toast
    } finally {
      setSharing(false);
    }
  };
  
  return (
    <div className="w-full max-w-md bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 rounded-2xl p-8 border border-purple-500/20 shadow-2xl">
      {/* Header */}
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-2">
          <div className="w-8 h-8 bg-gradient-to-br from-purple-400 to-blue-500 rounded-lg flex items-center justify-center">
            <span className="text-white text-sm font-bold">N</span>
          </div>
          <span className="text-purple-300 text-xs font-semibold">NEXPERT</span>
        </div>
        <span className="text-blue-400 text-xs font-medium px-2 py-1 bg-blue-500/10 rounded">
          AI-VERIFIED EXPERT
        </span>
      </div>
      
      {/* Profile Section */}
      <div className="flex items-center gap-4 mb-6">
        <div className="w-16 h-16 rounded-full border-2 border-purple-400 overflow-hidden bg-purple-500/20 flex-shrink-0">
          <img 
            src={cardData.profile.photo_url} 
            alt={cardData.profile.name}
            className="w-full h-full object-cover"
          />
        </div>
        <div>
          <h2 className="text-white text-xl font-bold">{cardData.profile.name}</h2>
          <p className="text-purple-300 text-sm">{cardData.profile.title}</p>
        </div>
      </div>
      
      {/* Achievement Title */}
      <h3 className="text-white text-2xl font-bold mb-6">{cardData.header.title}</h3>
      
      {/* Score Change */}
      <div className="bg-slate-800/50 rounded-lg p-4 mb-6 border border-purple-500/20">
        <div className="flex items-center justify-between mb-4">
          <div className="text-center">
            <p className="text-gray-400 text-xs uppercase tracking-widest">Yesterday</p>
            <p className="text-white text-2xl font-bold">
              {Math.round(cardData.metrics.score_before)}
            </p>
          </div>
          
          <div className="flex flex-col items-center gap-1">
            <ArrowUp className="w-5 h-5 text-green-400" />
            <p className="text-green-400 text-sm font-semibold">
              +{Math.round(cardData.metrics.point_gain)}
            </p>
          </div>
          
          <div className="text-center">
            <p className="text-gray-400 text-xs uppercase tracking-widest">Today</p>
            <p className="text-green-400 text-2xl font-bold">
              {Math.round(cardData.metrics.score_after)}
            </p>
          </div>
        </div>
      </div>
      
      {/* Achievements */}
      {cardData.achievements.length > 0 && (
        <div className="bg-slate-800/30 rounded-lg p-4 mb-6 space-y-3">
          <p className="text-gray-300 text-xs uppercase tracking-widest font-semibold">
            What changed today?
          </p>
          {cardData.achievements.map((achievement, i) => (
            <div key={i} className="flex items-center gap-3">
              <span className="text-lg">{achievement.icon}</span>
              <span className="text-gray-200 text-sm">{achievement.text}</span>
            </div>
          ))}
        </div>
      )}
      
      {/* Expertise Tags */}
      <div className="flex gap-2 mb-6 flex-wrap">
        {cardData.expertise_tags.map((tag, i) => (
          <span 
            key={i}
            className="text-xs text-purple-300 bg-purple-500/20 px-3 py-1 rounded-full border border-purple-500/30"
          >
            {tag}
          </span>
        ))}
      </div>
      
      {/* Ranking */}
      <div className="text-center mb-6 p-3 bg-purple-500/10 rounded-lg border border-purple-500/20">
        <p className="text-purple-300 text-sm">{cardData.ranking.label}</p>
      </div>
      
      {/* CTA */}
      <a 
        href={cardData.cta.url}
        className="w-full text-center px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-blue-700 transition mb-4 block"
      >
        {cardData.cta.text} →
      </a>
      
      {/* Share Button */}
      <button
        onClick={handleShare}
        disabled={sharing || card.shared_to_linkedin}
        className="w-full flex items-center justify-center gap-2 px-4 py-2 bg-slate-700/50 text-gray-300 font-semibold rounded-lg hover:bg-slate-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <Share2 className="w-4 h-4" />
        {card.shared_to_linkedin ? 'Shared to LinkedIn' : 'Share to LinkedIn'}
      </button>
      
      {/* Share Stats */}
      {card.shared_to_linkedin && (
        <div className="mt-4 text-center text-xs text-gray-400">
          <p>{card.impressions} impressions · {(card.engagement_rate * 100).toFixed(1)}% engagement</p>
        </div>
      )}
      
      {/* Footer */}
      <div className="border-t border-slate-700 mt-6 pt-4 text-center text-xs text-gray-400">
        <p className="mb-1">{cardData.footer.tagline}</p>
        <a href="https://nexpertapp.com" className="text-purple-400 hover:text-purple-300">
          nexpertapp.com
        </a>
      </div>
    </div>
  );
};

export default CredibilityCard;
```

---

## LinkedIn Share Integration

### Generate Share Text Dynamically

```php
function generateLinkedInShareText(int $expertId, array $cardData): string {
    $expert = getExpertProfile($pdo, $expertId);
    $topics = implode(' · ', array_slice($expert['expertise_topics'], 0, 3));
    
    $templates = [
        'milestone_crossed' => <<<TEXT
Just crossed {milestone} on Nexpert! 🚀

Over the last 90 days, my credibility score has grown by {point_gain} points through verified sessions, learner feedback and demonstrated expertise in {topics}.

Curious to see how Nexpert measures expert credibility differently from a traditional profile or follower count.

[View my Nexpert profile]
TEXT,
        
        'session_count' => <<<TEXT
Completed my {session_count}th verified expert session on Nexpert! ⭐

Each session brings me closer to mastery in {topics}. It's the consistency and learner outcomes that matter.

[See all verified sessions]
TEXT,
        
        'learner_outcome' => <<<TEXT
{certified_learners} learners have achieved their goals through my guidance. {satisfaction_rate}% satisfaction rate. 💬

This is what expert credibility is really about — not profiles, but outcomes.

Nexpert is building the infrastructure to make this measurable and visible.
TEXT,
    ];
    
    $template = $templates[$cardData['trigger_type']] ?? 'New credibility milestone unlocked!';
    
    return interpolate($template, [
        'milestone' => $cardData['milestone'] ?? '',
        'point_gain' => round($cardData['point_gain'] ?? 0),
        'topics' => $topics,
        'session_count' => $cardData['session_count'] ?? 0,
        'certified_learners' => $cardData['certified_learners'] ?? 0,
        'satisfaction_rate' => round($cardData['satisfaction_rate'] ?? 0)
    ]);
}
```

### LinkedIn API Integration

```php
/**
 * Share card to LinkedIn via LinkedIn Share API
 */
function shareToLinkedIn(int $expertId, int $cardId, string $customText = null): array {
    global $pdo;
    
    // Get expert's LinkedIn token
    $token = getLinkedInAccessToken($expertId);
    if (!$token) {
        throw new Exception("No LinkedIn integration for expert $expertId");
    }
    
    // Get card data
    $card = $pdo->prepare(
        "SELECT * FROM credibility_card_events WHERE id = ? AND expert_id = ?"
    );
    $card->execute([$cardId, $expertId]);
    $cardRow = $card->fetch(PDO::FETCH_ASSOC);
    
    // Generate share text
    $shareText = $customText ?? generateLinkedInShareText($expertId, json_decode($cardRow['trigger_condition'], true));
    
    // Call LinkedIn API
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.linkedin.com/v2/shares',
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'X-Requested-With: XMLHttpRequest'
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'owner' => 'urn:li:person:' . getLinkedInPersonId($expertId),
            'text' => [
                'text' => $shareText
            ],
            'content' => [
                'title' => 'Your Nexpert Credibility Update',
                'description' => 'View your verified expertise and achievements',
                'contentUrl' => 'https://nexpertapp.com/expert/' . $expertId,
                'contentImage' => generateCardImage($cardId)
            ],
            'distribution' => [
                'linkedInDistributionTarget' => []
            ]
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        $result = json_decode($response, true);
        
        // Update card record
        $pdo->prepare("
            UPDATE credibility_card_events SET
                shared_to_linkedin = 1,
                share_url = ?,
                shared_at = NOW()
            WHERE id = ?
        ")->execute(['https://www.linkedin.com/feed/', $cardId]);
        
        return ['success' => true, 'share_url' => 'https://www.linkedin.com/feed/'];
    }
    
    throw new Exception("LinkedIn API error: $response");
}
```

---

## Placement in Technical Documentation

### Where to Add This Section

This guide should be included in your existing trust engine documentation **after** the "What's Next?" section as:

**📍 Section: "User-Facing Dashboard & Social Integration"**

**Hierarchy:**
```
Expert Trust Engine Documentation
├── Executive Summary
├── Architecture Overview
├── Event Sequence & EMA Trace
├── 4-Dimension Trust Model
├── Band Classification Logic
├── Verification vs. Seeded Scores
├── Key Implementation Details
├── Production Readiness Checklist
├── Deployment & Monitoring
├── What's Next?
└─→ Daily Credibility Card System (NEW)
    ├── Overview & Trigger Events
    ├── Database Schema
    ├── Card Generation Engine
    ├── API Endpoints
    ├── React Component
    ├── LinkedIn Integration
    └── Scheduling & Monitoring
```

---

## Why This Matters: The Philosophy

### Static Badge Problem
```
Expert wakes up → "My Nexpert score is 847" → Shares every day → Audience ignores
Result: Badge becomes noise, not signal
```

### Event-Driven Solution
```
Expert has outcome → System detects milestone → Card is generated → "I crossed 850!" → Worth sharing
Result: Signal is genuine, sharing is authentic, Nexpert is the infrastructure
```

### Long-Term Value

By triggering cards only on real achievements:

1. **Experts share authentically** — They're not promoting Nexpert, they're celebrating their growth
2. **LinkedIn feeds stay clean** — No noise from daily static cards
3. **Nexpert becomes infrastructure** — Like how credit scores don't need ads; they just *exist* behind important decisions
4. **Data accumulates** — Impression, engagement, and sharing patterns become product research gold
5. **Reciprocal value** — LinkedIn traffic → Nexpert, Nexpert trust → LinkedIn credibility

---

## Monitoring & Metrics

### Dashboard Queries

```sql
-- Cards generated this week
SELECT trigger_type, COUNT(*) as count
FROM credibility_card_events
WHERE generated_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY trigger_type
ORDER BY count DESC;

-- Share rate by trigger type
SELECT 
  trigger_type,
  COUNT(*) as cards_generated,
  SUM(shared_to_linkedin) as cards_shared,
  ROUND(SUM(shared_to_linkedin) / COUNT(*) * 100, 2) as share_rate
FROM credibility_card_events
GROUP BY trigger_type;

-- Top performers by engagement
SELECT 
  e.name,
  c.card_id,
  c.impressions,
  ROUND(c.engagement_rate * 100, 2) as engagement_percent
FROM credibility_card_events c
JOIN users e ON c.expert_id = e.id
WHERE c.shared_to_linkedin = 1
ORDER BY c.impressions DESC
LIMIT 20;
```

---

## Future Enhancements

1. **A/B Testing**  
   Test different card templates and share texts to maximize engagement

2. **Predictive Notifications**  
   "You're 5 points away from the next milestone — here's what you need to hit it"

3. **Learner Feedback Cards**  
   "3 of your learners left 5-star reviews" (before they see the expert card)

4. **Comparative Benchmarks**  
   "You're +12 points ahead of experts in your tier"

5. **Animated Cards**  
   Lottie animations showing score progression in real-time

---

**Generated:** August 25, 2026  
**Platform:** Nexpert.ai v2  
**Author:** Lekha Bhan, Nexpert AI Architecture
