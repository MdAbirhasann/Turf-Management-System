CREATE DATABASE IF NOT EXISTS ts_sports_arena CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ts_sports_arena;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('manager','staff') DEFAULT 'manager',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(80) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(80),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    duration_minutes INT DEFAULT NULL,
    label VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    pricing_type ENUM('duration','per_person') DEFAULT 'duration',
    status ENUM('active','inactive') DEFAULT 'active',
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    method ENUM('bKash','Nagad') NOT NULL,
    transaction_id VARCHAR(100) NOT NULL,
    screenshot_path VARCHAR(255),
    status ENUM('pending','verified','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(60) NOT NULL UNIQUE,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    email VARCHAR(150),
    service_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    duration_minutes INT DEFAULT NULL,
    people_count INT DEFAULT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    note TEXT,
    payment_id INT DEFAULT NULL,
    status ENUM('Pending','Confirmed','Cancelled','Completed') DEFAULT 'Pending',
    created_by ENUM('customer','manager') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id),
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
    INDEX idx_booking_lookup (service_id, booking_date, start_time, end_time, status),
    INDEX idx_invoice (invoice_number),
    INDEX idx_customer (customer_name, phone)
) ENGINE=InnoDB;

CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    invoice_number VARCHAR(60) NOT NULL UNIQUE,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE blocked_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    block_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    reason VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    INDEX idx_blocked_lookup (service_id, block_date, start_time, end_time)
) ENGINE=InnoDB;

CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150),
    image_path VARCHAR(255) NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(120) NOT NULL,
    rating INT NOT NULL DEFAULT 5,
    comment TEXT NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO users (name, email, password, role) VALUES
('TS Sports Arena Manager', 'manager@tssportsarena.com', '$2y$12$cp3QEGZeAs2fEzXSd847ceMEeXl0HSN2w4hSPRZmEocqg6dunCJyu', 'manager');

INSERT INTO services (name, slug, description, icon) VALUES
('Football Turf', 'football', 'Premium football turf with flexible hourly slots.', 'football'),
('Badminton Court', 'badminton', 'Indoor badminton court booking for practice and matches.', 'badminton'),
('Swimming Pool', 'swimming', 'Swimming pool access with per-person pricing.', 'water');

INSERT INTO prices (service_id, duration_minutes, label, price, pricing_type) VALUES
(1, 60, '1 Hour', 2500, 'duration'),
(1, 90, '1.5 Hours', 3000, 'duration'),
(1, 120, '2 Hours', 4000, 'duration'),
(2, 60, '1 Hour', 1200, 'duration'),
(2, 90, '1.5 Hours', 1500, 'duration'),
(2, 120, '2 Hours', 2000, 'duration'),
(3, NULL, 'Per Person', 350, 'per_person');

INSERT INTO gallery (title, image_path, status) VALUES
('Football Turf', 'assets/images/logo.jpg', 'active'),
('Badminton Court', 'assets/images/logo.jpg', 'active'),
('Swimming Pool', 'assets/images/logo.jpg', 'active');

INSERT INTO reviews (customer_name, rating, comment, status) VALUES
('Arif Rahman', 5, 'Clean environment, fast booking, and excellent football turf experience.', 'active'),
('Nusrat Jahan', 5, 'The booking process was smooth and the staff were helpful.', 'active'),
('Tanvir Ahmed', 5, 'Great place for friends and family sports time.', 'active');
