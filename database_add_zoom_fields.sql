-- Add Zoom meeting related fields to bookings table
ALTER TABLE bookings 
  ADD COLUMN zoom_meeting_id VARCHAR(64) NULL AFTER status,
  ADD COLUMN zoom_join_url TEXT NULL AFTER zoom_meeting_id,
  ADD COLUMN zoom_start_url TEXT NULL AFTER zoom_join_url,
  ADD COLUMN zoom_created_at DATETIME NULL AFTER zoom_start_url;

-- Optional index if querying frequently by zoom_meeting_id
CREATE INDEX idx_bookings_zoom_meeting_id ON bookings (zoom_meeting_id);
