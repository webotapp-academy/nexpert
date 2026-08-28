-- Migration 003: Add new event types to trust_events
-- Adds: outcome_achieved, goal_completed, repeat_booking, session_no_show, late_start, profile_viewed

ALTER TABLE `trust_events`
  MODIFY COLUMN `event_type` ENUM(
    'session_completed',
    'booking_created',
    'feedback_submitted',
    'expert_profile_updated',
    'kyc_verified',
    'complaint_logged',
    'outcome_achieved',
    'goal_completed',
    'repeat_booking',
    'session_no_show',
    'late_start',
    'profile_viewed'
  ) NOT NULL;

SELECT 'Migration 003 complete: new event types added' AS status;
