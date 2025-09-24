<?php
$host = "localhost";
$dbname = "sports_manager";
$username = "root";
$password = "";

try { 
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") { 
        $teamID = $_POST['team_id'];
        $opponent = $_POST['opponent'];
        $gameDate = $_POST['game_date'];
        $location = $_POST['location'];

        $stmt = $pdo->prepare("INSERT INTO Games (team_id, opponent, game_date, location) 
                               VALUES (:team_id, :opponent, :game_date, :location)");
        $stmt->execute([
            'team_id' => $teamId,
            'opponent' => $opponent,
            'game_date' => $gameDate,
            'location' => $location
        ]);

        echo "<p style='color:green;'>Game added successfully!</p>";
    }

      $teams = $pdo->query("SELECT * FROM Teams ORDER BY team_name")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head><title>Add Game</title></head>
<body>
    <h2>Add a New Game</h2>
    <form method="POST">
        <label>Team:</label><br>
        <select name="team_id" required>
            <?php foreach ($teams as $team): ?>
                <option value="<?= $team['team_id'] ?>"><?= $team['team_name'] ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Opponent:</label><br>
        <input type="text" name="opponent" required><br><br>

        <label>Game Date:</label><br>
        <input type="date" name="game_date" required><br><br>

        <label>Location:</label><br>
        <input type="text" name="location"><br><br>

        <button type="submit">Add Game</button>
    </form>
</body>
</html>