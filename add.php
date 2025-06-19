<?php 
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $jersey = $_POST['jersey'];
    $position = $_POST['position'];
    $goals = $_POST['goals'];
    $assists = $_POST['assists'];

    $stmt = $conn->prepare("INSERT INTO players (name, jersey_number, position, goals, assists) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisii", $name, $jersey, $position, $goals, $assists);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Player</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Add New Player</h1>
    <form action="add.php" method="post">
       <label>Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Jersey Number:</label><br>
        <input type="number" name="jersey" required><br><br>

        <label>Position:</label><br>
        <input type="text" name="position" required><br><br>

        <label>Goals:</label><br>
        <input type="number" name="goals" value="0"><br><br>

        <label>Assists:</label><br>
        <input type="number" name="assists" value="0"><br><br>
        
        <button type="submit">Add Player</button>
    </form>
    <br>
    <a href="index.php"Back to Roster></a>
</body>
</html>