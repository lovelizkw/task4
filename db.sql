CREATE DATABASE IF NOT EXISTS u82353_anketa 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE u82353_anketa;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    bio TEXT,
    agreed TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE user_languages (
    user_id INT NOT NULL,
    language_id INT NOT NULL,
    PRIMARY KEY (user_id, language_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE
);

INSERT INTO languages (name) VALUES 
('Pascal'), ('C'), ('C++'), ('JavaScript'), ('PHP'), ('Python'),
('Java'), ('Haskell'), ('Clojure'), ('Prolog'), ('Scala'), ('Go');