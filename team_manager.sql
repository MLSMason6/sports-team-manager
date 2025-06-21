-- Create the database
CREATE DATABASE IF NOT EXISTS team_manager;
USE team_manager;

-- Create the players table
CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    jersey_number INT NOT NULL,
    position VARCHAR(50) NOT NULL,
    goals INT DEFAULT 0,
    assists INT DEFAULT 0
);
