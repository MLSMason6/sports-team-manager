<?php
include 'db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit();
}

// Fetch existing player data
$stmt = $conn->prepare("SELECT * FROM players WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$player = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $jersey = $_POST['jersey'];
    $position = $_POST['position'];
    $goals = $_POST['goals'];
    $assists = $_POST['assists'];

    $stmt = $conn->prepare("UPDATE players SET name=?, jersey_number=?, position=?, goals=?, assists=? WHERE id=?");
    $stmt->bind_param("sisiii", $name, $jersey, $position, $goals, $assists, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Player</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Edit Player</h1>
    <form method="post">
        <label>Name:</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($player['name']) ?>" required><br><br>

        <label>Jersey Number:</label><br>
        <input type="number" name="jersey" value="<?= $player['jersey_number'] ?>" required><br><br>

        <label>Position:</label><br>
        <input type="text" name="position" value="<?= htmlspecialchars($player['position']) ?>" required><br><br>

        <label>Goals:</label><br>
        <input type="number" name="goals" value="<?= $player['goals'] ?>"><br><br>

        <label>Assists:</label><br>
        <input type="number" name="assists" value="<?= $player['assists'] ?>"><br><br>

        <button type="submit">Update Player</button>
    </form>
    <br>
    <a href="index.php">← Back to Roster</a>
</body>
</html>
