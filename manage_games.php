<?php
$host = "localhost";
$dbname = "sports_manager";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM Games WHERE game_id = :id");
        $stmt->execute(['id' => $_GET['delete']]);
        echo "<p style='color:red;'>Game deleted successfully!</p>";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
        $stmt = $pdo->prepare("UPDATE Games 
                               SET opponent = :opponent, game_date = :game_date, location = :location
                               WHERE game_id = :id");
        $stmt->execute([
            'opponent' => $_POST['opponent'],
            'game_date' => $_POST['game_date'],
            'location' => $_POST['location'],
            'id' => $_POST['game_id']
        ]);
        echo "<p style='color:green;'>Game updated successfully!</p>";
    }

    $games = $pdo->query("SELECT g.*, t.team_name 
                          FROM Games g 
                          JOIN Teams t ON g.team_id = t.team_id 
                          ORDER BY g.game_date")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head><title>Manage Games</title></head>
<body>
    <h2>Manage Games</h2>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Team</th><th>Opponent</th><th>Date</th><th>Location</th><th>Actions</th></tr>
        <?php foreach ($games as $game): ?>
        <tr>
            <form method="POST">
                <td><?= $game['team_name'] ?></td>
                <td>
                    <input type="hidden" name="game_id" value="<?= $game['game_id'] ?>">
                    <input type="text" name="opponent" value="<?= $game['opponent'] ?>" required>
                </td>
                <td><input type="date" name="game_date" value="<?= $game['game_date'] ?>" required></td>
                <td><input type="text" name="location" value="<?= $game['location'] ?>"></td>
                <td>
                    <button type="submit" name="update">Update</button>
                    <a href="manage_games.php?delete=<?= $game['game_id'] ?>" onclick="return confirm('Delete this game?')">Delete</a>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
