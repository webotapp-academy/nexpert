-- Migration 005: New tables for MVP2
-- goals, outcomes, ai_insights, enterprise_leads

-- 5a: Learner goals
CREATE TABLE IF NOT EXISTS `goals` (
  `id`               INT AUTO_INCREMENT PRIMARY KEY,
  `learner_id`       INT NOT NULL,
  `raw_text`         TEXT,
  `goal_type`        VARCHAR(100) DEFAULT NULL,
  `structured_goal`  JSON DEFAULT NULL,
  `target_date`      DATE DEFAULT NULL,
  `status`           ENUM('active','achieved','abandoned') DEFAULT 'active',
  `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`learner_id`),
  FOREIGN KEY (`learner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5b: Verified outcomes
CREATE TABLE IF NOT EXISTS `outcomes` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `goal_id`       INT DEFAULT NULL,
  `session_id`    INT DEFAULT NULL,
  `expert_id`     INT NOT NULL,
  `learner_id`    INT NOT NULL,
  `achieved`      BOOLEAN DEFAULT FALSE,
  `outcome_type`  VARCHAR(100) DEFAULT NULL,
  `description`   TEXT DEFAULT NULL,
  `evidence_url`  VARCHAR(500) DEFAULT NULL,
  `validated_at`  TIMESTAMP NULL DEFAULT NULL,
  `validator`     ENUM('self','admin','ai') DEFAULT 'self',
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`expert_id`),
  INDEX (`learner_id`),
  FOREIGN KEY (`expert_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`learner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5c: AI-generated insights (explainability, growth recs, session summaries)
CREATE TABLE IF NOT EXISTS `ai_insights` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `entity_id`     INT NOT NULL,
  `entity_type`   ENUM('expert','session','booking','goal') NOT NULL,
  `insight_type`  ENUM('explainability','growth_recs','session_preparation','session_summary','goal_analysis') NOT NULL,
  `content_json`  JSON DEFAULT NULL,
  `model`         VARCHAR(50) DEFAULT 'gpt-4o-mini',
  `generated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`entity_id`, `insight_type`),
  INDEX (`entity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5d: Enterprise leads from landing page form
CREATE TABLE IF NOT EXISTS `enterprise_leads` (
  `id`              INT AUTO_INCREMENT PRIMARY KEY,
  `company_name`    VARCHAR(200) NOT NULL,
  `contact_name`    VARCHAR(200) DEFAULT NULL,
  `role`            VARCHAR(100) DEFAULT NULL,
  `company_size`    VARCHAR(50) DEFAULT NULL,
  `expert_count`    INT DEFAULT NULL,
  `problem_text`    TEXT DEFAULT NULL,
  `email`           VARCHAR(200) NOT NULL,
  `phone`           VARCHAR(20) DEFAULT NULL,
  `status`          ENUM('new','contacted','demo_scheduled','converted','lost') DEFAULT 'new',
  `notes`           TEXT DEFAULT NULL,
  `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`email`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT 'Migration 005 complete: goals, outcomes, ai_insights, enterprise_leads created' AS status;
