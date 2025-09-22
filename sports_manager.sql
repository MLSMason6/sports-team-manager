CREATE DATABASE sports_manager; 
USE sports_manager;

CREATE TABLE Teams ( 
    team_id INT AUTO_INCREMENT PRIMARY KEY, 
    team_name VARCHAR(100) NOT NULL, 
    coach_name VARCHAR(100), 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
);

CREATE TABLE Players ( 
    player_id INT AUTO_INCREMENT PRIMARY KEY, 
    team_id INT, 
    first_name VARCHAR(50) NOT NULL, 
    last_name VARCHAR(50) NOT NULL,
    position VARCHAR(50), 
    jersey_number INT, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (team_id) REFERENCES Teams(team_id) ON DELETE SET NULL 
);

CREATE TABLE Games (
    game_id INT AUTO_INCREMENT PRIMARY KEY, 
    team_id INT, 
    opponent VARCHAR (100) NOT NULL, 
    game_date DATE NOT NULL, 
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (team_id) REFERENCES Teams(team_id) ON DELETE CASCADE 
); 

CREATE TABLE Stats ( 
    stat_id INT AUTO_INCREMENT PRIMARY KEY, 
    player_id INT, 
    game_id INT, 
    points INT DEFAULT 0, 
    assists INT DEFAULT 0,
    rebounds INT DEFAULT 0,
    minutes_played INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (player_id) REFERENCES Players(player_id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES Games(game_id) ON DELETE CASCADE
);

-- Get all players on a team 
SELECT p.player_id, p.first_name, p.last_name, p.position, p.jersey_number 
FROM Players p 
JOIN Teams t ON p.team_id = t.team_id 
WHERE t.team_name = 'Lions';

-- Get schedule of games for a team
SELECT g.game_id, g.opponent, g.game_date, g.location
FROM Games g 
JOIN Teams t ON g.team_id = t.team_id 
WHERE t.team_name = 'Lions'
ORDER BY g.game_date;

-- Get stats for a specific player across all games
SELECT g.opponent, g.game_date, s.points, s.assists, s.rebounds, s.minutes_played
FROM Stats s 
JOIN Games g ON s.game_id = g.game_id 
JOIN Players p ON s.player_id = p.player_id 
WHERE p.first_name = 'John' AND p.last_name = 'Doe'
ORDER BY g.game_date; 

-- Get average stats per player
SELECT p.first_name, p.last_name
    AVG(s.points) AS avg_points, 
    AVG(s.assists) AS avg_assists, 
    AVG(s.rebounds) AS avg_rebounds
FROM Stats s 
Join Players p ON s.player_id = p.player_id 
GROUP BY p.player_id
ORDER BY avg_points DESC; 

-- Get total team points for each game
SELECT g.game_date, g.opponent, 
    SUM(s.points) AS team_points 
FROM Stats s 
JOIN Games g ON s.game_id = p.player_id 
WHERE g.team_id = 1 -- replace with your team_id 
GROUP BY g.game_id
ORDER BY g.game_date; 

-- Find top scorer on a team (all-time) 
SELECT p.first_name, p.last_name, SUM(s.points) AS total_points 
FROM Stats s 
JOIN Players p ON s.player_id = p.player_id 
JOIN Teams t ON p.team_id = t.team_id 
WHERE t.team_name = 'Lions' 
GROUP BY p.player_id 
ORDER BY total_points DESC 
LIMIT 1;