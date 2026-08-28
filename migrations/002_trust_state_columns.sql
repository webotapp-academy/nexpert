-- Migration 002: Add missing columns to trust_state
-- Run this FIRST before any other migration
-- Safe: all changes are additive (ADD COLUMN only, no DROP)

ALTER TABLE `trust_state`
  ADD COLUMN IF NOT EXISTS `is_frozen` TINYINT(1) NOT NULL DEFAULT 0 AFTER `consistency_score`,
  ADD COLUMN IF NOT EXISTS `band_name` ENUM('Sovereign','Established','Verified','Emerging','Unverified') NOT NULL DEFAULT 'Unverified' AFTER `is_frozen`,
  ADD COLUMN IF NOT EXISTS `confidence_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `band_name`,
  ADD COLUMN IF NOT EXISTS `trend_direction` ENUM('rising','stable','declining') NOT NULL DEFAULT 'stable' AFTER `confidence_score`;

-- Backfill band_name for existing rows based on current trust_tier
UPDATE `trust_state` SET `band_name` = 'Sovereign'   WHERE `trust_tier` = 'A' AND `overall_score` >= 90;
UPDATE `trust_state` SET `band_name` = 'Established'  WHERE `trust_tier` = 'A' AND `overall_score` < 90;
UPDATE `trust_state` SET `band_name` = 'Verified'     WHERE `trust_tier` = 'B' AND `overall_score` >= 60;
UPDATE `trust_state` SET `band_name` = 'Emerging'     WHERE `trust_tier` = 'B' AND `overall_score` < 60;
UPDATE `trust_state` SET `band_name` = 'Unverified'   WHERE `trust_tier` = 'C';

SELECT 'Migration 002 complete: trust_state columns added' AS status;
