<?php
$host = "localhost";
$dbname = "sports_manager";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $teams = $pdo->query("SELECT team_id, team_name FROM Teams ORDER BY team_name")->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_GET['team_id'])) {
        $teamId = $_GET['team_id'];

        $stmt = $pdo->prepare("SELECT g.game_date, g.opponent, SUM(s.points) as team_points
                               FROM Stats s
                               JOIN Games g ON s.game_id = g.game_id
                               WHERE g.team_id = :team_id
                               GROUP BY g.game_id
                               ORDER BY g.game_date");
        $stmt->execute(['team_id' => $teamId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $team = $pdo->query("SELECT team_name FROM Teams WHERE team_id = $teamId")->fetch(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head><title>Team Report</title></head>
<body>
    <h2>Team Report</h2>
    <form method="GET">
        <label>Select Team:</label>
        <select name="team_id" required>
            <?php foreach ($teams as $t): ?>
                <option value="<?= $t['team_id'] ?>" <?= (isset($teamId) && $teamId == $t['team_id']) ? 'selected' : '' ?>>
                    <?= $t['team_name'] ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">View Report</button>
    </form>

    <?php if (!empty($results)): ?>
        <h3>Game Results for <?= $team['team_name'] ?></h3>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr><th>Date</th><th>Opponent</th><th>Total Points</th></tr>
            <?php foreach ($results as $r): ?>
            <tr>
                <td><?= $r['game_date'] ?></td>
                <td><?= $r['opponent'] ?></td>
                <td><?= $r['team_points'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
