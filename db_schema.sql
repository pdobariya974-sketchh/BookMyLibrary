-- Minimal schema for BookMyLibrary
-- Create database and essential tables used by the app.

CREATE DATABASE IF NOT EXISTS `library_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `library_db`;

-- users table (simple roles: admin, librarian, user)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','librarian','user') NOT NULL DEFAULT 'user'
);

-- Ensure required `users` columns exist before inserting sample rows.
-- Older MySQL versions may not support "ADD COLUMN IF NOT EXISTS",
-- so create and call a small stored procedure that checks information_schema
-- and adds missing columns/indexes safely.

DELIMITER $$
DROP PROCEDURE IF EXISTS ensure_users_columns$$
CREATE PROCEDURE ensure_users_columns()
BEGIN
  -- add missing columns if they don't exist
  IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role'
  ) THEN
    ALTER TABLE users ADD COLUMN role ENUM('admin','librarian','user') NOT NULL DEFAULT 'user';
  END IF;

  IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'name'
  ) THEN
    ALTER TABLE users ADD COLUMN name VARCHAR(100) NOT NULL;
  END IF;

  IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email'
  ) THEN
    ALTER TABLE users ADD COLUMN email VARCHAR(150) NOT NULL;
  END IF;

  IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'password'
  ) THEN
    ALTER TABLE users ADD COLUMN password VARCHAR(255) NOT NULL;
  END IF;

  -- add unique index on email if missing
  IF NOT EXISTS (
    SELECT * FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'uniq_email'
  ) THEN
    ALTER TABLE users ADD UNIQUE INDEX uniq_email (email);
  END IF;
END$$

CALL ensure_users_columns()$$
DROP PROCEDURE IF EXISTS ensure_users_columns$$
DELIMITER ;

-- sample accounts (passwords are plain for test; change to hashed in production)
INSERT INTO `users` (`name`,`email`,`password`,`role`) VALUES
('Admin User','admin@gmail.com','123456','admin'),
('Library','library@gmail.com','123456','librarian'),
('Normal User','user@gmail.com','123456','user')
ON DUPLICATE KEY UPDATE email=email;

-- (Optional) ensure an index on email so ON DUPLICATE KEY works; this will fail on
-- older MySQL versions that don't support IF NOT EXISTS for indexes, so leave it commented
-- unless you know your server supports it.
-- ALTER TABLE `users` ADD UNIQUE INDEX IF NOT EXISTS `uniq_email` (`email`);

-- books table (minimal)
CREATE TABLE IF NOT EXISTS `books` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `author` VARCHAR(150),
  `category` VARCHAR(100),
  `year` INT,
  `library` VARCHAR(150),
  `total_copies` INT DEFAULT 1,
  `available_copies` INT DEFAULT 1,
  `image` VARCHAR(255)
);

-- issued_books (minimal)
CREATE TABLE IF NOT EXISTS `issued_books` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `book_id` INT NOT NULL,
  `issue_date` DATE,
  `return_date` DATE,
  `status` VARCHAR(50),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- You can expand this schema with fines, libraries, categories etc.
