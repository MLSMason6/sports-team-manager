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

    if ($_SERVER["REQUEST_METHOD"] == "POST") { 
        $teamName = $_POST['team_name'];
        $coachName = $_POST['coach_name']; 

        $stmt = $pdo->prepare("INSERT INTO Teams (team_name, coach_name) VALUES (:team_name, :coach_name)");
        $stmt->execute(['team_name' => $teamName, 'coach_name' => $coachName]);

        echo "<p style='color:green;'>Team added successfully!</p>";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?> 

<!DOCTYPE html>
<html>
<head><title>Add Team</title></head>
<body>
    <h2>Add a New Team</h2>
    <form method="POST">
        <label>Team Name:</label><br>
        <input type="text" name="team_name" required><br><br>

        <label>Coach Name:</label><br>
        <input type="text" name="coach_name"><br><br>

        <button type="submit">Add Team</button>
    </form>
</body>
</html>