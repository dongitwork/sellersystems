CREATE DATABASE IF NOT EXISTS user_management;
USE user_management;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    api_token VARCHAR(80) UNIQUE,
    remember_token VARCHAR(100),
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_status (status)
);

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_roles (
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- Insert default roles
INSERT INTO roles (name, slug, description) VALUES
('Administrator', 'admin', 'Full system access'),
('Seller', 'seller', 'Can manage products and orders'),
('Customer', 'customer', 'Regular user access');

-- Insert admin user (password: admin123)
INSERT INTO users (name, username, email, password, api_token, status) VALUES
('Admin User', 'admin', 'admin@example.com', '$2y$10$YLxqY2rY0K7uDJSSKcB0OuBQN3C0kNrtKKjH1C/fgLHRbLEP6Jmhq', 'admin_token_123456789', 'active');

-- Assign admin role
INSERT INTO user_roles (user_id, role_id) VALUES (1, 1);