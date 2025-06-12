CREATE TABLE players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    jersey_number INT,
    position VARCHAR(50),
    goals INT DEFAULT 0,
    assists INT DEFAULT 0
);