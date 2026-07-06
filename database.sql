-- Database schema for Makueni Digital Youth Hub
-- Run: mysql -u root -p < database.sql

DROP DATABASE IF EXISTS makueni_digital_hub;
CREATE DATABASE makueni_digital_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE makueni_digital_hub;

-- ict_centres: list of ICT training centres
CREATE TABLE ict_centres (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  centre_name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- admins: administrative users (centre_admin / super_admin)
CREATE TABLE admins (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  centre_id INT UNSIGNED DEFAULT NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'centre_admin',
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_admins_centre (centre_id),
  CONSTRAINT fk_admins_centre FOREIGN KEY (centre_id) REFERENCES ict_centres(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- students: registered learners
CREATE TABLE students (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  fullname VARCHAR(255) NOT NULL,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  phone VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  centre_id INT UNSIGNED NOT NULL,
  registration_fee_status VARCHAR(50) DEFAULT 'pending',
  training_fee_note TEXT,
  training_start_date DATE DEFAULT NULL,
  training_status VARCHAR(50) DEFAULT 'Upcoming',
  completion_status VARCHAR(50) DEFAULT 'incomplete',
  status VARCHAR(20) DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_students_centre (centre_id),
  CONSTRAINT fk_students_centre FOREIGN KEY (centre_id) REFERENCES ict_centres(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- notes: training materials uploaded by admins
CREATE TABLE notes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  file_name VARCHAR(255) NOT NULL,
  centre_id INT UNSIGNED NOT NULL,
  uploaded_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_notes_centre (centre_id),
  KEY idx_notes_uploaded_by (uploaded_by),
  CONSTRAINT fk_notes_centre FOREIGN KEY (centre_id) REFERENCES ict_centres(id) ON DELETE CASCADE,
  CONSTRAINT fk_notes_admin FOREIGN KEY (uploaded_by) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- reviews: student feedback for centres
CREATE TABLE reviews (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_id INT UNSIGNED NOT NULL,
  centre_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  review TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_reviews_student (student_id),
  KEY idx_reviews_centre (centre_id),
  CONSTRAINT fk_reviews_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_centre FOREIGN KEY (centre_id) REFERENCES ict_centres(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- admin_sessions: login activity history for admins
CREATE TABLE admin_sessions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_id INT UNSIGNED NOT NULL,
  login_time DATETIME NOT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  activity VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_admin_sessions_admin (admin_id),
  CONSTRAINT fk_admin_sessions_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional: seed a few ICT centres (edit as needed)
INSERT INTO ict_centres (centre_name) VALUES
('Makueni ICT Centre A'),
('Makueni ICT Centre B');

-- NOTE:
-- 1) To create the initial super admin, run the included PHP script:
--      php create_admin.php
--    That script uses PHP's password_hash() to store a secure password.

-- 2) If you prefer to insert an admin via SQL, first generate a bcrypt hash
--    (e.g. using PHP) and then run a statement like the example below
--    (replace <BCRYPT_HASH> with the generated hash):
--
-- INSERT INTO admins(username, password, role, status, centre_id) VALUES
-- ('superadmin', '<BCRYPT_HASH>', 'super_admin', 'active', NULL);

-- 3) The application expects to find ICT centres in `ict_centres`.
--    Adjust seeded centre names above as needed.
