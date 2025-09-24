<?php
// Database connection settings 
$host = "localhost";
$dbname = "sports_manager"; 
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM Teams WHERE team_id = :id");
        $stmt->execute(['id' => $_GET['delete']]);
        echo "<p style='color:red;'>Team deleted successfully!</p>";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
        $stmt = $pdo->prepare("UPDATE Teams SET team_name = :team_name, coach_name = :coach_name WHERE team_id = :id");
        $stmt->execute([
            'team_name' => $_POST['team_name'],
            'coach_name' => $_POST['coach_name'],
            'id' => $_POST['team_id']
        ]);
        echo "<p style='color:green;'>Team updated successfully!</p>";
    }

    $teams = $pdo->query("SELECT * FROM Teams ORDER BY team_name")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head><title>Manage Teams</title></head>
<body>
    <h2>Manage Teams</h2>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Team</th><th>Coach</th><th>Actions</th></tr>
        <?php foreach ($teams as $team): ?>
        <tr>
            <form method="POST">
                <td>
                    <input type="hidden" name="team_id" value="<?= $team['team_id'] ?>">
                    <input type="text" name="team_name" value="<?= $team['team_name'] ?>" required>
                </td>
                <td><input type="text" name="coach_name" value="<?= $team['coach_name'] ?>"></td>
                <td>
                    <button type="submit" name="update">Update</button>
                    <a href="manage_teams.php?delete=<?= $team['team_id'] ?>" onclick="return confirm('Delete this team?')">Delete</a>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>