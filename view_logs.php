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

    // --- Load filter options ---
    $users = $pdo->query("SELECT user_id, username FROM Users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);

    // --- Handle filters --- 
    $conditions = [];
    $params = [];

    if (!empty($_GET['user_id'])) {
         $conditions[] = "a.user_id = :user_id";
        $params['user_id'] = $_GET['user_id'];
    }
    if (!empty($_GET['action_keyword'])) {
        $conditions[] = "(a.action LIKE :keyword OR a.details LIKE :keyword)";
        $params['keyword'] = "%" . $_GET['action_keyword'] . "%";
    }
    if (!empty($_GET['start_date'])) {
        $conditions[] = "DATE(a.created_at) >= :start_date";
        $params['start_date'] = $_GET['start_date'];
    }
    if (!empty($_GET['end_date'])) { 
        $conditions[] = "DATE(a.created_at) <= :end_date";
        $params['end_date'] = $_GET['end_date'];
    }

    $whereClause = "";
    if (count($conditions) > 0) { 
        $whereClause = "WHERE" . implode(" AND ", $conditions);
    }

    $sql = "SELECT a.log_id, u.username, a.action, a.details, a.created_at
            FROM AuditLog a 
            JOIN Users u ON a.user_id = u.user_id
            $whereClause 
            ORDER BY a.created_at DESC 
            LIMIT 200";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Audit Logs - Filters</title>
    <style>
            body { font-family: Arial, sans-serif; background: #f7f7f7; padding: 20px; }
            h2 { color: #2c3e50; }
            form { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
            label { font-weight: bold; margin-right: 10px; }
            input, select { margin-right: 15px; }
            table { border-collapse: collapse; width: 100%; background: white; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #2c3e50; color: white; }
            tr:nth-child(even) { background: #f9f9f9; }
            a { color: #2980b9; text-decoration: none; }
            a:hover { text-decoration: underline; }
        </style>
</head>
<body>
    <h2>Audit Logs (Filter & Search)</h2>

    <form method="GET">
            <label>User:</label>
            <select name="user_id">
                <option value="">All Users</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['user_id'] ?>" <?= (isset($_GET['user_id']) && $_GET['user_id'] == $user['user_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
    
            <label>Keyword:</label>
            <input type="text" name="action_keyword" value="<?= $_GET['action_keyword'] ?? '' ?>" placeholder="Search action or details">
    
            <label>From:</label>
            <input type="date" name="start_date" value="<?= $_GET['start_date'] ?? '' ?>">
    
            <label>To:</label>
            <input type="date" name="end_date" value="<?= $_GET['end_date'] ?? '' ?>">
    
            <button type="submit">Apply Filters</button>
            <a href="view_logs.php">Reset</a>
        </form>
    <table>
        <tr><th>ID</th><th>User</th><th>Action</th><th>Details</th><th>Date</th></tr>
        <?php if (empty($logs)): ?>
            <tr><td colspan="5" style="text-align:center;">No logs found for selected filters.</td></tr>
        <?php else: ?>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= $log['log_id'] ?></td>
                <td><?= htmlspecialchars($log['username']) ?></td>
                <td><?= htmlspecialchars($log['action']) ?></td>
                <td><?= htmlspecialchars($log['details']) ?></td>
                <td><?= $log['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

    <p><a href="dashboard.php">⬅ Back to Dashboard</a></p>
</body>
</html>