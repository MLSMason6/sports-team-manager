<?php
include 'db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sports Team Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Team Roster</h1>
    <a href="add.php">+ Add New Player</a>

    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Name</th>
            <th>Jersey #</th>
            <th>Position</th>
            <th>Goals</th>
            <th>Assists</th>
            <th>Actions</th>
        </tr>

        <?php
        $result = $conn->query("SELECT * FROM players");

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . $row['jersey_number'] . "</td>";
            echo "<td>" . $row['position'] . "</td>";
            echo "<td>" . $row['goals'] . "</td>";
            echo "<td>" . $row['assists'] . "</td>";
            echo "<td>
                    <a href='edit.php?id=" . $row['id'] . "'>Edit</a> | 
                    <a href='delete.php?id=" . $row['id'] . "' onclick=\"return confirm('Are you sure?')\">Delete</a>
                  </td>";
            echo "</tr>";
        }
        ?>
    </table>
</body>
</html>
