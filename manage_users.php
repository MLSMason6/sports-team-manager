<?php 
session_start(); 
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php")
    exit();
}
if ($_SESSION['role'] !== 'superadmin') { 
    die("Access denied. Only Super Admins can manage users.");
}

$host = "localhost";
$dbname = "sports_manager";
$username = "root";
$password = "";

try { 
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['delete'])) { 
        $userId = $_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM Users WHERE user_id = :id");
        $stmt->execute(['id' => $userId]);
        echo "<p style='color:red;'>User deleted successfully!</p>";
    }

    $users = $pdo->query("SELECT user_id, username, role, created_at FROM Users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head><title>Manage Users</title></head>
<body>
    <h2>Manage Users</h2>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>ID</th><th>Username</th><th>Role</th><th>Created</th><th>Actions</th></tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user['user_id'] ?></td>
            <td><?= $user['username'] ?></td>
            <td><?= $user['role'] ?></td>
            <td><?= $user['created_at'] ?></td>
            <td>
                <a href="manage_users.php?delete=<?= $user['user_id'] ?>" onclick="return confirm('Delete this user?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><a href="register.php">➕ Register New User</a></p>
    <p><a href="dashboard.php">⬅ Back to Dashboard</a></p>
</body>
</html>