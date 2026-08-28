-- Migration 006: Daily Credibility Card System
-- Creates tables for event-triggered shareable credibility cards

CREATE TABLE IF NOT EXISTS credibility_card_events (
  id                    INT PRIMARY KEY AUTO_INCREMENT,
  expert_id             INT NOT NULL,
  trigger_type          VARCHAR(50) NOT NULL,
  trigger_condition     JSON,
  card_data             JSON,
  score_before          DECIMAL(6, 2),
  score_after           DECIMAL(6, 2),
  point_gain            DECIMAL(6, 2),
  generated_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
  shared_to_linkedin    TINYINT DEFAULT 0,
  shared_at             DATETIME,
  share_url             VARCHAR(255),
  impressions           INT DEFAULT 0,
  engagement_rate       DECIMAL(5, 3),
  
  FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (expert_id, generated_at),
  INDEX (trigger_type, generated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS credibility_card_templates (
  id                    INT PRIMARY KEY AUTO_INCREMENT,
  trigger_type          VARCHAR(50) NOT NULL,
  variant               VARCHAR(20) DEFAULT 'a',
  title_template        VARCHAR(255),
  subtitle_template     VARCHAR(255),
  body_json             JSON,
  cta_text              VARCHAR(100),
  is_active             TINYINT DEFAULT 1,
  created_at            DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Default Templates
INSERT INTO credibility_card_templates (trigger_type, variant, title_template, subtitle_template, body_json, cta_text) VALUES
('milestone_crossed', 'a', '🎯 You crossed {milestone} Credibility Points', 'Daily Credibility Update', '{"highlight": "Milestone Achieved"}', 'View my verified profile'),
('session_count', 'a', '⭐ Your {milestone}th verified expert session', 'Daily Credibility Update', '{"highlight": "Session Milestone"}', 'View my verified profile'),
('ranking_jump', 'a', '📈 Moved from #{rank_before} → #{rank_after} this week', 'Daily Credibility Update', '{"highlight": "Rank Leaderboard"}', 'View my verified profile'),
('expertise_recognition', 'a', '🧠 You are now top {percentile}% in {topic}', 'Daily Credibility Update', '{"highlight": "Expertise Recognition"}', 'View my verified profile'),
('learner_outcome', 'a', '💬 {satisfaction_rate}% of learners rated your sessions highly', 'Daily Credibility Update', '{"highlight": "Outcome Verification"}', 'View my verified profile'),
('credibility_growth', 'a', '🚀 +{growth_amount} credibility points in {period_days} days', 'Daily Credibility Update', '{"highlight": "Fast Growth Velocity"}', 'View my verified profile'),
('band_promotion', 'a', '🏅 You have earned {to_band} status', 'Daily Credibility Update', '{"highlight": "Trust Tier Upgrade"}', 'View my verified profile'),
('top_performer', 'a', '👑 Top {percentile}% of AI Experts on Nexpert', 'Daily Credibility Update', '{"highlight": "Top Tier Excellence"}', 'View my verified profile')
ON DUPLICATE KEY UPDATE title_template = VALUES(title_template);
