-- Library Management System Database Schema
-- Run this SQL file to set up the database

CREATE DATABASE IF NOT EXISTS library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_db;

-- Admin table
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Books table
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    category VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    available INT NOT NULL DEFAULT 1,
    description TEXT,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Members table
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    membership_date DATE NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB;

-- Issued books table
CREATE TABLE IF NOT EXISTS issued_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    member_id INT NOT NULL,
    issue_date DATE NOT NULL,
    return_date DATE NOT NULL,
    actual_return_date DATE DEFAULT NULL,
    status ENUM('issued','returned','overdue') DEFAULT 'issued',
    fine DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Default admin: password = admin123
INSERT INTO admin (username, password, email) VALUES
('admin', MD5('admin123'), 'admin@library.com')
ON DUPLICATE KEY UPDATE username = username;

-- Sample books
INSERT INTO books (title, author, isbn, category, quantity, available, description) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', '978-0743273565', 'Fiction', 5, 5, 'A story of the fabulously wealthy Jay Gatsby and his love for the beautiful Daisy Buchanan.'),
('To Kill a Mockingbird', 'Harper Lee', '978-0061935466', 'Fiction', 4, 4, 'A gripping story of racial injustice and childhood innocence in the American South.'),
('1984', 'George Orwell', '978-0451524935', 'Dystopian', 6, 6, 'A chilling prophecy about the future of the world.'),
('Clean Code', 'Robert C. Martin', '978-0132350884', 'Technology', 3, 3, 'A handbook of agile software craftsmanship.'),
('The Pragmatic Programmer', 'Andrew Hunt', '978-0135957059', 'Technology', 3, 3, 'Your journey to mastery in software development.'),
('Introduction to Algorithms', 'Thomas H. Cormen', '978-0262033848', 'Technology', 2, 2, 'Comprehensive introduction to the modern study of computer algorithms.'),
('Sapiens', 'Yuval Noah Harari', '978-0062316097', 'History', 5, 5, 'A brief history of humankind from the Stone Age to the twenty-first century.'),
('Thinking, Fast and Slow', 'Daniel Kahneman', '978-0374533557', 'Psychology', 4, 4, 'Exploration of the two systems that drive the way we think.'),
('The Alchemist', 'Paulo Coelho', '978-0062315007', 'Fiction', 6, 6, 'A magical story about following your dreams.'),
('Harry Potter and the Philosopher''s Stone', 'J.K. Rowling', '978-0439708180', 'Fantasy', 7, 7, 'The first book in the Harry Potter series.'),
('Atomic Habits', 'James Clear', '978-0735211292', 'Self-Help', 5, 5, 'Tiny changes, remarkable results.'),
('The Art of War', 'Sun Tzu', '978-1599869773', 'History', 4, 4, 'An ancient Chinese military treatise dating from the 5th century BC.')
ON DUPLICATE KEY UPDATE title = title;

-- Sample members
INSERT INTO members (name, email, phone, address, membership_date, status) VALUES
('Alice Johnson', 'alice@example.com', '555-0101', '123 Elm Street, Springfield', CURDATE(), 'active'),
('Bob Smith', 'bob@example.com', '555-0102', '456 Oak Avenue, Shelbyville', CURDATE(), 'active'),
('Carol White', 'carol@example.com', '555-0103', '789 Pine Road, Capital City', CURDATE(), 'active'),
('David Brown', 'david@example.com', '555-0104', '321 Maple Lane, Ogdenville', CURDATE(), 'active'),
('Eva Martinez', 'eva@example.com', '555-0105', '654 Cedar Blvd, North Haverbrook', CURDATE(), 'active'),
('Frank Wilson', 'frank@example.com', '555-0106', '987 Birch Street, Brockway', CURDATE(), 'inactive')
ON DUPLICATE KEY UPDATE name = name;
