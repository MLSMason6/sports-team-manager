<?php
$host = "localhost";
$dbname = "sports_manager";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $playerId = $_POST['player_id'];
        $gameId = $_POST['game_id'];
        $points = $_POST['points'];
        $assists = $_POST['assists'];
        $rebounds = $_POST['rebounds'];
        $minutes = $_POST['minutes_played'];

        $stmt = $pdo->prepare("INSERT INTO Stats (player_id, game_id, points, assists, rebounds, minutes_played)
                               VALUES (:player_id, :game_id, :points, :assists, :rebounds, :minutes)");
        $stmt->execute([
            'player_id' => $playerId,
            'game_id' => $gameId,
            'points' => $points,
            'assists' => $assists,
            'rebounds' => $rebounds,
            'minutes' => $minutes
        ]);

        require_once "log_action.php";
        logAction($pdo, $_SESSION['user_id'], "Added stats", "Player ID: $playerId, Game ID: $gameId, Points: $points");

        echo "<p style='color:green;'>Stats added successfully!</p>";
    }

    $players = $pdo->query("SELECT player_id, first_name, last_name FROM Players ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);
    $games = $pdo->query("SELECT game_id, opponent, game_date FROM Games ORDER BY game_date")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head><title>Add Stats</title></head>
<body>
    <h2>Add Stats</h2>
    <form method="POST">
        <label>Player:</label><br>
        <select name="player_id" required>
            <?php foreach ($players as $p): ?>
                <option value="<?= $p['player_id'] ?>"><?= $p['first_name'] . " " . $p['last_name'] ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Game:</label><br>
        <select name="game_id" required>
            <?php foreach ($games as $g): ?>
                <option value="<?= $g['game_id'] ?>">vs <?= $g['opponent'] ?> (<?= $g['game_date'] ?>)</option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Points:</label><br>
        <input type="number" name="points" value="0"><br><br>

        <label>Assists:</label><br>
        <input type="number" name="assists" value="0"><br><br>

        <label>Rebounds:</label><br>
        <input type="number" name="rebounds" value="0"><br><br>

        <label>Minutes Played:</label><br>
        <input type="number" name="minutes_played" value="0"><br><br>

        <button type="submit">Save Stats</button>
    </form>
</body>
</html>
