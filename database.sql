-- Delegates Table
CREATE TABLE delegates (
    delegate_uid INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    father_name VARCHAR(255),
    dob DATE,
    mobile_number VARCHAR(15) UNIQUE,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    church_name VARCHAR(255),
    pastor_name VARCHAR(255),
    baptism BOOLEAN,
    holy_spirit_anointing BOOLEAN,
    spiritual_calling TEXT,
    ministry_involvement TEXT,
    salvation_testimony TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Meetings Table
CREATE TABLE meetings (
    meeting_id VARCHAR(50) PRIMARY KEY,
    meeting_title VARCHAR(255) NOT NULL,
    meeting_theme VARCHAR(255),
    meeting_venue TEXT,
    meeting_host VARCHAR(255),
    meeting_dates DATE,
    meeting_timing TIME,
    registration_contact VARCHAR(15),
    special_instructions TEXT,
    meeting_status ENUM('Active', 'Completed', 'Cancelled') DEFAULT 'Active'
);

-- Delegate Meeting Enrollment Table
CREATE TABLE delegate_meeting_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    delegate_uid INT,
    mobile_number VARCHAR(15),
    name VARCHAR(255),
    meeting_id VARCHAR(50),
    badge_number VARCHAR(50) UNIQUE,
    feedback_submitted BOOLEAN DEFAULT FALSE,
    permit BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (delegate_uid) REFERENCES delegates(delegate_uid),
    FOREIGN KEY (meeting_id) REFERENCES meetings(meeting_id)
);

-- SEF Submissions Table
CREATE TABLE sef_submissions (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    delegate_uid INT,
    mobile_number VARCHAR(15),
    meeting_id VARCHAR(50),
    submission_type ENUM('text', 'file', 'audio') NOT NULL,
    submission_data TEXT,
    submission_guid VARCHAR(100) UNIQUE,
    submission_datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(50),
    device_info TEXT,
    review_status ENUM('In Queue', 'On Hold', 'Escalated', 'Done') DEFAULT 'In Queue',
    reviewer_name VARCHAR(255),
    reviewer_datetime TIMESTAMP,
    reviewer_comments TEXT,
    rating INT CHECK (rating BETWEEN 0 AND 10),
    rating_locked BOOLEAN DEFAULT FALSE
);

-- Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_role ENUM('admin', 'meeting_access', 'gatekeeper') NOT NULL
);

-- Audit Trail Table
CREATE TABLE sef_audit_trail (
    audit_id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT,
    action VARCHAR(255),
    old_data TEXT,
    new_data TEXT,
    username VARCHAR(50),
    audit_datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(50)
);