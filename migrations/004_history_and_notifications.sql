-- Migration 004: Add trigger_event_id to history, create notification queue

-- 4a: Link score history to the event that triggered it
ALTER TABLE `trust_state_history`
  ADD COLUMN IF NOT EXISTS `trigger_event_id` INT DEFAULT NULL AFTER `stability_score`,
  ADD COLUMN IF NOT EXISTS `band_name` ENUM('Sovereign','Established','Verified','Emerging','Unverified') DEFAULT 'Unverified' AFTER `trigger_event_id`,
  ADD COLUMN IF NOT EXISTS `confidence_score` DECIMAL(5,2) DEFAULT 0.00 AFTER `band_name`;

-- Foreign key only if trust_events exists (safe add)
ALTER TABLE `trust_state_history`
  ADD CONSTRAINT IF NOT EXISTS `fk_history_event`
  FOREIGN KEY (`trigger_event_id`) REFERENCES `trust_events`(`id`) ON DELETE SET NULL;

-- 4b: Trust score change notification queue
CREATE TABLE IF NOT EXISTS `trust_notifications` (
  `id`               INT AUTO_INCREMENT PRIMARY KEY,
  `expert_id`        INT NOT NULL,
  `score_old`        DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `score_new`        DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `delta`            DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `band_old`         VARCHAR(20) DEFAULT NULL,
  `band_new`         VARCHAR(20) DEFAULT NULL,
  `signal_type`      VARCHAR(50) DEFAULT NULL,
  `event_type`       VARCHAR(80) DEFAULT NULL,
  `explanation_text` TEXT,
  `email_sent_at`    TIMESTAMP NULL DEFAULT NULL,
  `read_at`          TIMESTAMP NULL DEFAULT NULL,
  `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`expert_id`),
  INDEX (`email_sent_at`),
  FOREIGN KEY (`expert_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT 'Migration 004 complete: history enhanced, notifications table created' AS status;
