CREATE DATABASE cyber_track_db;
USE cyber_track_db;

CREATE TABLE groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE tracking_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id VARCHAR(255) NOT NULL,
    contact VARCHAR(255) NOT NULL,
    os VARCHAR(255),
    browser VARCHAR(255),
    ip VARCHAR(255),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

INSERT INTO users (email, password) VALUES ('admin@example.com', 'admin123');
