<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] !== 'admin') {
    die("Access denied. Admins only.");
}

// Database connection settings 
$host = "localhost";
$dbname = "sports_manager"; 
$username = "root";
$password = "";

try { 
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle DELETE 
    if (isset($_GET['delete'])) { 
        $playerId = $_GET['delete']; 
        $stmt =$pdo->prepare("DELETE FROM Players WHERE player_id = :player_id");
        $stmt->execute(['player_id' => $playerId]);
        echo "<p style='color:red;'>Player deleted successfully!</p>";
    }

    // Handle UPDATE 
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) { 
        $playerId = $_POST['player_id'];
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $position = $_POST['position'];
        $jersey = $_POST['jersey_number'];

        $stmt = $pdo->prepare("UPDATE Players 
                               SET first_name = :first_name, last_name = :last_name, 
                                   position = :position, jersey_number = :jersey_number
                               WHERE player_id = :player_id");

        $stmt->execute([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'position' => $position,
            'jersey_number' => $jersey,
            'player_id' => $playerId 
        ]); 

        echo "<p style='color:green;'>Player updated successfully!</p>";
    }

    // Get list of players 
    $players = $pdo->query("SELECT * FROM Players ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) { 
    echo "Connection failed: " . $e->getMessage();
} 
?> 

<!DOCTYPE html>
<html>
<head>
    <title>Manage Players</title>
</head>
<body>
    <h2>Manage Players</h2>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Name</th>
            <th>Position</th>
            <th>Jersey #</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($players as $player): ?>
         <tr>
            <form method="POST">
                <td>
                    <input type="hidden" name="player_id" value="<?= $player['player_id'] ?>">
                    <input type="text" name="first_name" value="<?= $player['first_name'] ?>" required>
                    <input type="text" name="last_name" value="<?= $player['last_name'] ?>" required>
                </td>
                <td><input type="text" name="position" value="<?= $player['position'] ?>"></td>
                <td><input type="number" name="jersey_number" value="<?= $player['jersey_number'] ?>"></td>
                <td>
                    <button type="submit" name="update">Update</button>
                    <a href="manage_players.php?delete=<?= $player['player_id'] ?>" onclick="return confirm('Delete this player?')">Delete</a>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>