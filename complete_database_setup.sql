-- ============================================
-- NEXPERT.AI COMPLETE DATABASE SETUP
-- Run this file to create the complete database structure
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS nexpert_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nexpert_ai;

-- ============================================
-- USERS & AUTHENTICATION
-- ============================================

-- Main Users Table (Combined for Learners, Experts, and Admins)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('learner', 'expert', 'admin') NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    google_id VARCHAR(255) NULL,
    phone_verified BOOLEAN DEFAULT FALSE,
    email_verified BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'suspended', 'deleted') DEFAULT 'active',
    last_login DATETIME NULL,
    last_device VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OTP Management Table
CREATE TABLE otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NULL,
    otp_code VARCHAR(6) NOT NULL,
    purpose ENUM('login', 'signup', 'verification', 'password_reset') NOT NULL,
    expires_at DATETIME NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_phone (phone),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- LEARNER PROFILE & PREFERENCES
-- ============================================

CREATE TABLE learner_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    profile_photo VARCHAR(500) NULL,
    age INT NULL,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say') NULL,
    location VARCHAR(255) NULL,
    goals TEXT NULL,
    preferences JSON NULL,
    language_preference VARCHAR(50) DEFAULT 'en',
    timezone VARCHAR(100) DEFAULT 'UTC',
    notification_email BOOLEAN DEFAULT TRUE,
    notification_sms BOOLEAN DEFAULT TRUE,
    notification_whatsapp BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- EXPERT PROFILE & VERIFICATION
-- ============================================

CREATE TABLE expert_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    profile_photo VARCHAR(500) NULL,
    tagline VARCHAR(255) NULL,
    bio_short TEXT NULL,
    bio_full TEXT NULL,
    expertise_verticals JSON NULL,
    credentials TEXT NULL,
    experience_years INT NULL,
    timezone VARCHAR(100) DEFAULT 'UTC',
    verification_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    verified_at DATETIME NULL,
    govt_id_url VARCHAR(500) NULL,
    certification_urls JSON NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    rating_average DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    total_sessions INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_verification (verification_status),
    INDEX idx_featured (is_featured),
    INDEX idx_rating (rating_average)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Expert Verification Logs (for Admin tracking)
CREATE TABLE expert_verification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    admin_id INT NOT NULL,
    status ENUM('approved', 'rejected') NOT NULL,
    review_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- KYC VERIFICATION TABLE
-- ============================================

CREATE TABLE expert_kyc_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT UNIQUE NOT NULL,
    
    -- Personal Information
    full_legal_name VARCHAR(255) NOT NULL,
    date_of_birth DATE NOT NULL,
    nationality VARCHAR(100) NOT NULL,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say') NULL,
    
    -- Address Information
    address_line1 VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    
    -- Identity Documents
    id_document_type ENUM('passport', 'drivers_license', 'aadhaar', 'pan', 'national_id') NOT NULL,
    id_number VARCHAR(100) NOT NULL,
    id_document_front_url VARCHAR(500) NULL,
    id_document_back_url VARCHAR(500) NULL,
    
    -- Bank Details
    account_holder_name VARCHAR(255) NOT NULL,
    bank_name VARCHAR(255) NOT NULL,
    account_number VARCHAR(100) NOT NULL,
    ifsc_code VARCHAR(50) NOT NULL COMMENT 'IFSC/Swift/Routing Code',
    account_type ENUM('savings', 'current') NOT NULL,
    
    -- Verification Status
    verification_status ENUM('draft', 'pending', 'approved', 'rejected') DEFAULT 'draft',
    submitted_at DATETIME NULL,
    reviewed_at DATETIME NULL,
    reviewed_by INT NULL COMMENT 'Admin ID who reviewed',
    review_notes TEXT NULL,
    rejection_reason TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_expert (expert_id),
    INDEX idx_status (verification_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- KYC Verification History (Track status changes)
CREATE TABLE expert_kyc_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kyc_id INT NOT NULL,
    expert_id INT NOT NULL,
    previous_status ENUM('draft', 'pending', 'approved', 'rejected') NOT NULL,
    new_status ENUM('draft', 'pending', 'approved', 'rejected') NOT NULL,
    changed_by INT NULL COMMENT 'Admin ID',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (kyc_id) REFERENCES expert_kyc_verification(id) ON DELETE CASCADE,
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_kyc (kyc_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- EXPERT PRICING & AVAILABILITY
-- ============================================

CREATE TABLE expert_pricing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    pricing_type ENUM('per_session', 'package', 'subscription') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'INR',
    duration_minutes INT NULL,
    sessions_count INT NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_expert (expert_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE expert_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    day_of_week TINYINT NOT NULL COMMENT '0=Sunday, 6=Saturday',
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_expert (expert_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE expert_blackout_dates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    blackout_date DATE NOT NULL,
    reason VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_expert_date (expert_id, blackout_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- WORKFLOWS / PROGRAMS (SoW)
-- ============================================

CREATE TABLE workflows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    goal_outcome TEXT NULL,
    duration_weeks INT NULL,
    description TEXT NULL,
    is_template BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_expert (expert_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE workflow_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    workflow_id INT NOT NULL,
    step_order INT NOT NULL,
    step_type ENUM('session', 'assignment', 'followup', 'survey') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    duration_minutes INT NULL,
    resources JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workflow_id) REFERENCES workflows(id) ON DELETE CASCADE,
    INDEX idx_workflow (workflow_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BOOKINGS & SESSIONS
-- ============================================

CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    learner_id INT NOT NULL,
    expert_id INT NOT NULL,
    workflow_id INT NULL,
    pricing_id INT NULL,
    session_datetime DATETIME NOT NULL,
    duration_minutes INT NOT NULL,
    status ENUM('pending', 'confirmed', 'rescheduled', 'cancelled', 'completed') DEFAULT 'pending',
    join_link VARCHAR(500) NULL,
    recording_url VARCHAR(500) NULL,
    session_notes TEXT NULL,
    reschedule_reason TEXT NULL,
    cancel_reason TEXT NULL,
    calendar_sync_token VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (learner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (workflow_id) REFERENCES workflows(id) ON DELETE SET NULL,
    FOREIGN KEY (pricing_id) REFERENCES expert_pricing(id) ON DELETE SET NULL,
    INDEX idx_learner (learner_id),
    INDEX idx_expert (expert_id),
    INDEX idx_status (status),
    INDEX idx_datetime (session_datetime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE session_resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    uploaded_by INT NOT NULL COMMENT 'user_id of uploader',
    resource_type ENUM('document', 'link', 'video', 'image', 'other') NOT NULL,
    file_url VARCHAR(500) NULL,
    file_name VARCHAR(255) NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ASSIGNMENTS & SUBMISSIONS
-- ============================================

CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NULL,
    workflow_id INT NULL,
    expert_id INT NOT NULL,
    learner_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    due_date DATE NULL,
    resources JSON NULL,
    completion_criteria TEXT NULL,
    status ENUM('pending', 'submitted', 'completed', 'overdue') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (workflow_id) REFERENCES workflows(id) ON DELETE SET NULL,
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (learner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_learner (learner_id),
    INDEX idx_expert (expert_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE assignment_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    submission_text TEXT NULL,
    submission_file_url VARCHAR(500) NULL,
    feedback TEXT NULL,
    submitted_at DATETIME NULL,
    reviewed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PAYMENTS & TRANSACTIONS
-- ============================================

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    learner_id INT NOT NULL,
    expert_id INT NOT NULL,
    payment_gateway_id VARCHAR(255) NULL COMMENT 'Razorpay/Stripe ID',
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'INR',
    payment_type ENUM('one_time', 'package', 'subscription') NOT NULL,
    status ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
    commission_percentage DECIMAL(5,2) DEFAULT 20.00,
    commission_amount DECIMAL(10,2) NULL,
    expert_payout_amount DECIMAL(10,2) NULL,
    refund_amount DECIMAL(10,2) NULL,
    refund_reason TEXT NULL,
    payment_date DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (learner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_expert (expert_id),
    INDEX idx_learner (learner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE expert_payouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'INR',
    status ENUM('pending', 'processed', 'failed') DEFAULT 'pending',
    payout_date DATETIME NULL,
    payout_reference VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_expert (expert_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- REVIEWS & RATINGS
-- ============================================

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    learner_id INT NOT NULL,
    expert_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT NULL,
    status ENUM('pending', 'approved', 'flagged', 'removed') DEFAULT 'pending',
    admin_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (learner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_expert (expert_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- LEARNER PROGRESS TRACKING
-- ============================================

CREATE TABLE learner_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    learner_id INT NOT NULL,
    expert_id INT NOT NULL,
    workflow_id INT NULL,
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    total_sessions INT DEFAULT 0,
    completed_sessions INT DEFAULT 0,
    total_assignments INT DEFAULT 0,
    completed_assignments INT DEFAULT 0,
    expert_notes TEXT NULL COMMENT 'Private notes by expert',
    last_interaction_date DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (learner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (workflow_id) REFERENCES workflows(id) ON DELETE SET NULL,
    INDEX idx_learner_expert (learner_id, expert_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE follow_up_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    learner_id INT NOT NULL,
    reminder_datetime DATETIME NOT NULL,
    message TEXT NULL,
    status ENUM('pending', 'sent', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (learner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_datetime (reminder_datetime),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- NOTIFICATIONS
-- ============================================

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('booking', 'payment', 'assignment', 'reminder', 'payout', 'cancellation', 'system') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    delivery_channel SET('in_app', 'email', 'sms', 'whatsapp') DEFAULT 'in_app',
    status ENUM('pending', 'sent', 'failed', 'read') DEFAULT 'pending',
    read_at DATETIME NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ADMIN PANEL - MARKETING TOOLS
-- ============================================

CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_type ENUM('flat', 'percentage') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'INR',
    valid_from DATETIME NOT NULL,
    valid_until DATETIME NOT NULL,
    usage_limit_per_user INT DEFAULT 1,
    total_usage_limit INT NULL,
    times_used INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE coupon_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    user_id INT NOT NULL,
    booking_id INT NULL,
    discount_applied DECIMAL(10,2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    target_url VARCHAR(500) NULL,
    position ENUM('homepage_hero', 'homepage_sidebar', 'dashboard') DEFAULT 'homepage_hero',
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ADMIN PANEL - SUPPORT & TICKETS
-- ============================================

CREATE TABLE support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category ENUM('payment', 'booking', 'technical', 'abuse', 'other') NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    subject VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    assigned_admin_id INT NULL,
    resolution_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_admin_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_assigned (assigned_admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ticket_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ADMIN PANEL - DISPUTE MANAGEMENT
-- ============================================

CREATE TABLE payment_disputes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    booking_id INT NOT NULL,
    raised_by INT NOT NULL COMMENT 'user_id who raised dispute',
    category ENUM('refund_request', 'quality_issue', 'cancellation', 'technical', 'other') NOT NULL,
    description TEXT NOT NULL,
    status ENUM('pending', 'investigating', 'resolved', 'rejected') DEFAULT 'pending',
    resolution_notes TEXT NULL,
    resolved_by INT NULL COMMENT 'admin_id',
    resolved_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (raised_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CATEGORIES / VERTICALS (Reference Data)
-- ============================================

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT NULL,
    icon_url VARCHAR(500) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Expert-Category Mapping (Many-to-Many)
CREATE TABLE expert_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_expert_category (expert_id, category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SYSTEM SETTINGS & CONFIGURATIONS
-- ============================================

CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NULL,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ACTIVITY LOGS (for audit trail)
-- ============================================

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NULL COMMENT 'booking, payment, user, etc.',
    entity_id INT NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('platform_commission_percentage', '20.00', 'number', 'Platform commission on each booking (percentage)'),
('currency_default', 'INR', 'string', 'Default platform currency'),
('min_booking_advance_hours', '24', 'number', 'Minimum hours required to book in advance'),
('razorpay_key_id', '', 'string', 'Razorpay Key ID'),
('razorpay_key_secret', '', 'string', 'Razorpay Key Secret'),
('smtp_host', 'smtp.gmail.com', 'string', 'SMTP Host'),
('smtp_port', '587', 'number', 'SMTP Port'),
('smtp_username', '', 'string', 'SMTP Username'),
('smtp_password', '', 'string', 'SMTP Password');

-- Insert default categories
INSERT INTO categories (name, slug, description, is_active, display_order) VALUES
('Technology', 'technology', 'Programming, AI/ML, Data Science, Web Development', TRUE, 1),
('Business', 'business', 'Entrepreneurship, Management, Strategy, Marketing', TRUE, 2),
('Design', 'design', 'UI/UX, Graphic Design, Product Design', TRUE, 3),
('Finance', 'finance', 'Investment, Trading, Financial Planning', TRUE, 4),
('Career', 'career', 'Career Coaching, Interview Prep, Resume Building', TRUE, 5),
('Health & Wellness', 'health-wellness', 'Fitness, Nutrition, Mental Health', TRUE, 6),
('Education', 'education', 'Academic Tutoring, Test Prep, Language Learning', TRUE, 7),
('Creative Arts', 'creative-arts', 'Music, Writing, Photography, Art', TRUE, 8);

-- Create default admin user
INSERT INTO users (role, email, password_hash, status, email_verified, created_at) VALUES
('admin', 'admin@nexpert.ai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', TRUE, NOW());

-- Create admin profile (though not strictly needed, keeping it consistent)
SET @admin_id = LAST_INSERT_ID();

SELECT 'Database setup completed successfully! You can now run your application.' as Status;
SELECT CONCAT('Admin login: admin@nexpert.ai | Password: password (change this!)') as DefaultCredentials;
SELECT CONCAT('Total tables created: ', COUNT(*)) as TablesCount FROM information_schema.tables WHERE table_schema = 'nexpert_ai';