CREATE TABLE invite_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invite_code VARCHAR(50) NOT NULL,
    device_fingerprint VARCHAR(100) NULL,
    ip VARCHAR(50) NOT NULL,
    user_agent VARCHAR(255) NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);