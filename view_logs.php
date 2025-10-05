<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] !== 'superadmin') {
    die("Access denied. Only Super Admins can view logs.");
}

$host = "localhost";
$dbname = "sports_manager";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $logs = $pdo->query("SELECT a.log_id, u.username, a.action, a.details, a.created_at
                         FROM AuditLog a
                         JOIN Users u ON a.user_id = u.user_id
                         ORDER BY a.created_at DESC
                         LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head><title>Audit Logs</title></head>
<body>
    <h2>Audit Logs (Last 50 Actions)</h2>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>ID</th><th>User</th><th>Action</th><th>Details</th><th>Date</th></tr>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= $log['log_id'] ?></td>
            <td><?= $log['username'] ?></td>
            <td><?= $log['action'] ?></td>
            <td><?= $log['details'] ?></td>
            <td><?= $log['created_at'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><a href="dashboard.php">⬅ Back to Dashboard</a></p>
</body>
</html>