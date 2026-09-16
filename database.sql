-- ======================================================
-- WEDDING INVITATION SYSTEM V2 - MULTI-USER
-- Database: wedding_invitations02
-- ======================================================

CREATE DATABASE IF NOT EXISTS wedding_invitations02;
USE wedding_invitations02;

-- ======================================================
-- TABLE: master_admin (Only one master admin)
-- ======================================================
DROP TABLE IF EXISTS master_admin;
CREATE TABLE master_admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- TABLE: user_admins (Wedding organizers/users)
-- ======================================================
DROP TABLE IF EXISTS user_admins;
CREATE TABLE user_admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    couple_name VARCHAR(100),
    wedding_date DATE,
    is_active TINYINT(1) DEFAULT 1,
    account_status ENUM('active', 'suspended', 'pending') DEFAULT 'pending',
    created_by INT, -- Reference to master_admin.id
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_is_active (is_active),
    INDEX idx_account_status (account_status),
    FOREIGN KEY (created_by) REFERENCES master_admin(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- TABLE: invitations (Each user has one invitation)
-- ======================================================
DROP TABLE IF EXISTS invitations;
CREATE TABLE invitations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_admin_id INT NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    couple_names VARCHAR(100) NOT NULL,
    bride_name VARCHAR(50) NOT NULL,
    groom_name VARCHAR(50) NOT NULL,
    couple_headline VARCHAR(100) NOT NULL,
    wedding_date VARCHAR(50) NOT NULL,
    month VARCHAR(10) NOT NULL,
    day VARCHAR(5) NOT NULL,
    year VARCHAR(10) NOT NULL,
    countdown_target DATETIME NOT NULL,
    sub_headline TEXT,
    venue_title VARCHAR(100) NOT NULL,
    venue_location VARCHAR(100) NOT NULL,
    venue_address VARCHAR(200) NOT NULL,
    google_maps_url VARCHAR(255) NOT NULL,
    event_time VARCHAR(50) NOT NULL,
    whatsapp_phone VARCHAR(20) NOT NULL,
    primary_color VARCHAR(7) DEFAULT '#bc9c6c',
    secondary_color VARCHAR(7) DEFAULT '#f4efe6',
    text_color VARCHAR(7) DEFAULT '#443838',
    hero_image VARCHAR(255) DEFAULT 'hero-couple.jpg',
    bride_image VARCHAR(255) DEFAULT 'bride.jpg',
    groom_image VARCHAR(255) DEFAULT 'groom.jpg',
    intro_video VARCHAR(255) DEFAULT 'intro-open.mp4',
    intro_audio VARCHAR(255) DEFAULT 'intro-music1.mp3',
    bg_audio VARCHAR(255) DEFAULT 'intro-music.mp3',
    is_active TINYINT(1) DEFAULT 1,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_admin_id (user_admin_id),
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active),
    FOREIGN KEY (user_admin_id) REFERENCES user_admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- TABLE: schedule_items (Wedding Timeline)
-- ======================================================
DROP TABLE IF EXISTS schedule_items;
CREATE TABLE schedule_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invitation_id INT NOT NULL,
    time VARCHAR(20) NOT NULL,
    title VARCHAR(100) NOT NULL,
    description VARCHAR(200) NOT NULL,
    icon VARCHAR(50) DEFAULT 'fa-star',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (invitation_id) REFERENCES invitations(id) ON DELETE CASCADE,
    INDEX idx_invitation_id (invitation_id),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- TABLE: gallery_images (Wedding Gallery)
-- ======================================================
DROP TABLE IF EXISTS gallery_images;
CREATE TABLE gallery_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invitation_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption VARCHAR(200),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invitation_id) REFERENCES invitations(id) ON DELETE CASCADE,
    INDEX idx_invitation_id (invitation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- TABLE: table_finder (Find Your Table)
-- ======================================================
DROP TABLE IF EXISTS table_finder;
CREATE TABLE table_finder (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invitation_id INT NOT NULL,
    guest_name VARCHAR(100) NOT NULL,
    table_number VARCHAR(20) NOT NULL,
    family_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invitation_id) REFERENCES invitations(id) ON DELETE CASCADE,
    INDEX idx_invitation_id (invitation_id),
    INDEX idx_guest_name (guest_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- TABLE: rsvp_responses (Guest RSVP Responses)
-- ======================================================
DROP TABLE IF EXISTS rsvp_responses;
CREATE TABLE rsvp_responses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invitation_id INT NOT NULL,
    guest_name VARCHAR(100) NOT NULL,
    guest_email VARCHAR(100),
    attendance_status ENUM('attending', 'declined', 'pending') DEFAULT 'pending',
    number_of_guests INT DEFAULT 1,
    message TEXT,
    responded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invitation_id) REFERENCES invitations(id) ON DELETE CASCADE,
    INDEX idx_invitation_id (invitation_id),
    INDEX idx_attendance_status (attendance_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- TABLE: guest_book (Guest Messages)
-- ======================================================
DROP TABLE IF EXISTS guest_book;
CREATE TABLE guest_book (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invitation_id INT NOT NULL,
    guest_name VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    is_approved TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invitation_id) REFERENCES invitations(id) ON DELETE CASCADE,
    INDEX idx_invitation_id (invitation_id),
    INDEX idx_is_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- INSERT MASTER ADMIN (Password: master123)
-- ======================================================
INSERT INTO master_admin (username, password_hash, email, full_name) 
VALUES (
    'master',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'master@wedding.com',
    'Master Administrator'
);

-- ======================================================
-- INSERT SAMPLE USER ADMIN (Password: user123)
-- ======================================================
INSERT INTO user_admins (username, password_hash, email, full_name, couple_name, wedding_date, is_active, account_status, created_by) 
VALUES (
    'sahan',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'sahan@wedding.com',
    'Sahan Perera',
    'Sahan & Malki',
    '2026-07-11',
    1,
    'active',
    1
);

-- ======================================================
-- INSERT SAMPLE INVITATION FOR USER
-- ======================================================
INSERT INTO invitations (
    user_admin_id,
    slug,
    couple_names,
    bride_name,
    groom_name,
    couple_headline,
    wedding_date,
    month,
    day,
    year,
    countdown_target,
    sub_headline,
    venue_title,
    venue_location,
    venue_address,
    google_maps_url,
    event_time,
    whatsapp_phone,
    primary_color,
    secondary_color,
    text_color,
    hero_image,
    bride_image,
    groom_image,
    intro_video,
    intro_audio,
    bg_audio
) VALUES (
    1,
    'sahan-malki',
    'Sahan & Malki',
    'Malki',
    'Sahan',
    'Sahan & Malki',
    'JULY 11 2026',
    'JULY',
    '11',
    '2026',
    '2026-07-11 17:00:00',
    'Today marks the beginning of our forever. Built on love, trust, and endless laughter...',
    'Aldwark Manor Estate',
    'York',
    'Aldwark, Alne, York',
    'https://maps.google.com',
    '5:00 PM To 11:00 PM',
    '94771234567',
    '#bc9c6c',
    '#f4efe6',
    '#443838',
    'hero-couple.jpg',
    'bride.jpg',
    'groom.jpg',
    'intro-open.mp4',
    'intro-music1.mp3',
    'intro-music.mp3'
);

-- ======================================================
-- INSERT SAMPLE SCHEDULE ITEMS
-- ======================================================
INSERT INTO schedule_items (invitation_id, time, title, description, icon, sort_order) VALUES
(1, '5:00 PM', 'Guest Arrival', 'Welcome & seating', 'fa-location-dot', 1),
(1, '5:30 PM', 'The Ceremony', 'Exchange of vows', 'fa-heart', 2),
(1, '6:30 PM', 'Drinks & Photos', 'Drinks & celebration begins', 'fa-microphone', 3),
(1, '7:30 PM', 'Dinner', 'Delightful wedding feast', 'fa-utensils', 4),
(1, '9:30 PM', 'First Dance', 'Couple\'s first dance', 'fa-music', 5),
(1, '10:00 PM', 'Party Time', 'Speeches and farewells', 'fa-wand-magic-sparkles', 6),
(1, '11:00 PM', 'Going away', 'A beautiful send-off', 'fa-moon', 7);

-- ======================================================
-- INSERT SAMPLE GALLERY IMAGES
-- ======================================================
INSERT INTO gallery_images (invitation_id, image_path, caption, sort_order) VALUES
(1, 'gallery1.jpg', 'Beautiful ceremony', 1),
(1, 'gallery2.jpg', 'Reception hall', 2),
(1, 'gallery3.jpg', 'Wedding cake', 3);

-- ======================================================
-- INSERT SAMPLE TABLE FINDER ENTRIES
-- ======================================================
INSERT INTO table_finder (invitation_id, guest_name, table_number, family_name) VALUES
(1, 'John Doe', 'Table 1', 'Doe Family'),
(1, 'Jane Smith', 'Table 2', 'Smith Family'),
(1, 'Robert Johnson', 'Table 3', 'Johnson Family');