-- Add reschedule columns to bookings table
ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS reschedule_requested TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS reschedule_new_datetime DATETIME NULL,
ADD COLUMN IF NOT EXISTS reschedule_reason TEXT NULL,
ADD COLUMN IF NOT EXISTS reschedule_requested_by ENUM('learner', 'expert') NULL,
ADD COLUMN IF NOT EXISTS reschedule_requested_at DATETIME NULL;

-- Add index for faster queries
ALTER TABLE bookings ADD INDEX idx_reschedule_requested (reschedule_requested);
