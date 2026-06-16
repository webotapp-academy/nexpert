-- Database Migration: Update Expert Categories
-- Target: nexpert_ai (expert_profiles table)

UPDATE expert_profiles SET category = 'career Growth' WHERE category = 'coach';
UPDATE expert_profiles SET category = 'Leadership' WHERE category = 'mentor';
UPDATE expert_profiles SET category = 'Product&Strategy' WHERE category = 'consultant';
UPDATE expert_profiles SET category = 'AI & Technology' WHERE category = 'trainer';
UPDATE expert_profiles SET category = 'Entrepreneurship' WHERE category = 'freelancer';
