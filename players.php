<?php
// Database connection settings 
$host = "localhost";
$dbname = "sports_manager"; 
$username = "root";
$password = ""; 

try { 
    // Create PDO connection 
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE< PDO::ERRMODE_EXCEPTION);

    // Get all players for a specific team 
    $teamName = "Lions";
    $stmt = $pdo->prepare("
    SELECT p.player_id, p.first_name, p.last_name, p.position, p.jersey_number
    FROM Players p
    JOIN Teams t ON p.team_id = t.team_id
    WHERE t.team_name = :teamName
    ");
    $stmt->execute(['teamName' => $teamName]);

    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display results 
    echo "<h2>Players for Team: $teamName</h2>";
    echo "<ul>";
    foreach ($players as $player) {
        echo "<li>{$player['first_name']} {$player['last_name']} - #{$player['jersey_number']} ({$player['position']})</li>";
    }
    echo "</ul>";
} catch (PDOException $e) { 
    echo "Connection failed: " . $e->getMessage();
}
?>