CREATE DATABASE cyber_awareness;

USE cyber_awareness;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(150),
    awareness_score INT DEFAULT 100,
    risk_level ENUM('Low', 'Medium', 'High') DEFAULT 'Low'
);

CREATE TABLE click_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    link_url TEXT,
    is_malicious BOOLEAN,
    severity_level ENUM('Low', 'Medium', 'High', 'Critical'),
    click_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
