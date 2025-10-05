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
    // Create PDO connection 
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle from submission 
    if ($_SERVER["REQUEST_METHOD"] == "POST") { 
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $position = $_POST['position'];
        $jersey = $_POST['jersey_number'];
        $teamId = $_POST['team_id'];

        $stmt = $pdo->prepare("INSERT INTO Players (team_id, first_name, last_name, position, jersey_number) 
                               VALUES (:team_id, :first_name, :last_name, :position, :jersey_number)");
        
        $stmt ->execute([
            'team_id' => $teamId, 
            'first_name' => $firstName,
            'last_name' => $lastName,
            'position' => $position,
            'jersey_number' => $jersey
        ]);

        require_once "log_action.php";
        logAction($pdo, $_SESSION['user_id'], "Added player", "Name: $firstName $lastName, Team ID: $teamId");

        echo "<p style='color:green;'>Player added successfully!</p>";
    }

    // Get list of teams for dropdown
     $teams = $pdo->query("SELECT team_id, team_name FROM Teams")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) { 
    echo "Connection failed: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Player</title>
</head>
<body>
    <h2>Add a New Player</h2>
    <form method="POST">
        <label>First Name:</label><br>
        <input type="text" name="first_name" required><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" required><br><br>

        <label>Position:</label><br>
        <input type="text" name="position"><br><br>

        <label>Jersey Number:</label><br>
        <input type="text" name="jersey_number"><br><br>

        <label>Team:</label><br>
        <select name="team_id" required>
            <?php foreach ($teams as $team): ?>
               <option value="<?= $team['team_id'] ?>"><?= $team['team_name'] ?></option> 
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">Add Player</button>
    </form>
</body>
</html>