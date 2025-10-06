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
