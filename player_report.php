<?php
$host = "localhost";
$dbname = "sports_manager";
$username = "root";
$password = "";

try { 
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['player_id'])) { 
        $playerId = $_GET['player_id'];

        $stmt = $pdo->prepare("SELECT g.opponent, g.game_date, s.points, s.assists, s.rebounds, s.minutes_played
                               FROM Stats s
                               JOIN Games g ON s.game_id = g.game_id
                               WHERE s.player_id = :player_id
                               ORDER BY g.game_date");
        $stmt->execute(['player_id' => $playerId]);
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $player = $pdo->query("SELECT first_name, last_name FROM Players WHERE player_id = $playerId")->fetch(PDO::FETCH_ASSOC);
    }

    $players = $pdo->query("SELECT player_id, first_name, last_name FROM Players ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head><title>Player Report</title></head>
<body>
    <h2>Player Report</h2>
    <form method="GET">
        <label>Select Player:</label>
        <select name="player_id" required>
            <?php foreach ($players as $p): ?>
                <option value="<?= $p['player_id'] ?>" <?= (isset($playerId) && $playerId == $p['player_id']) ? 'selected' : '' ?>>
                    <?= $p['first_name'] . " " . $p['last_name'] ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">View Report</button>
    </form>

    <?php if (!empty($stats)): ?>
        <h3>Stats for <?= $player['first_name'] . " " . $player['last_name'] ?></h3>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr><th>Opponent</th><th>Date</th><th>Points</th><th>Assists</th><th>Rebounds</th><th>Minutes</th></tr>
            <?php foreach ($stats as $s): ?>
            <tr>
                <td><?= $s['opponent'] ?></td>
                <td><?= $s['game_date'] ?></td>
                <td><?= $s['points'] ?></td>
                <td><?= $s['assists'] ?></td>
                <td><?= $s['rebounds'] ?></td>
                <td><?= $s['minutes_played'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>