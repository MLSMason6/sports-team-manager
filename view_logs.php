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

    $users = $pdo->query("SELECT user_id, username FROM Users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);

    // --- Filters --- 
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

    $whereClause = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

    // --- Pagination --- 
    $perPage = 50; 
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $perPage;

    $countSql = "SELECT COUNT(*) FROM AuditLog a JOIN Users u ON a.user_id = u.user_id $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalLogs = $countStmt->fetchColumn();
    $totalPages = ceil($totalLogs / $perPage);

    $sql = "SELECT a.log_id, u.username, a.action, a.details, a.created_at
            FROM AuditLog a 
            JOIN Users u ON a.user_id = u.user_id
            $whereClause 
            ORDER BY a.created_at DESC 
            LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    foreach($params as $key => &$value) { $stmt->bindParam(":$key", $value);}
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- CSV Export ---
    if (isset($_GET['export']) && $_GET['export'] === 'csv') { 
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="audit_logs.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Log ID', 'User', 'Action', 'Details', 'Date']);
        foreach ($logs as $log) {
            fputcsv($output, [$log['log_id'], $log['username'], $log['action'], $log['details'], $log['created_at']]); 
        } 
        fclose($output); 
        exit();
    }

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Audit Logs - Filters & Exports</title>
    <style>
            body { font-family: Arial, sans-serif; background: #f7f7f7; padding: 20px; }
            h2 { color: #2c3e50; }
            form { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
            label { font-weight: bold; margin-right: 10px; }
            input, select { margin-right: 15px; }
            button { cursor: pointer; }
            table { border-collapse: collapse; width: 100%; background: white; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #2c3e50; color: white; }
            tr:nth-child(even) { background: #f9f9f9; }
            a { color: #2980b9; text-decoration: none; }
            a:hover { text-decoration: underline; }
            .pagination { margin-top: 15px; text-align: center; }
                    .pagination a, .pagination span {
                        display: inline-block;
                        margin: 0 5px;
                        padding: 6px 12px;
                        border-radius: 5px;
                        background: #2c3e50;
                        color: white;
                        text-decoration: none;
                    }
                    .pagination span { background: gray; }
                    /* Modal styles */
                            .modal {
                                display: none; 
                                position: fixed; 
                                z-index: 1000; 
                                left: 0; top: 0;
                                width: 100%; height: 100%; 
                                overflow: auto; 
                                background-color: rgba(0,0,0,0.6);
                            }
                            .modal-content {
                                background-color: #fff;
                                margin: 10% auto; 
                                padding: 20px; 
                                border-radius: 8px; 
                                width: 60%;
                                box-shadow: 0 2px 10px rgba(0,0,0,0.3);
                            }
                            .close-btn {
                                float: right; 
                                font-size: 20px; 
                                cursor: pointer; 
                                color: #2c3e50;
                            }
        </style>
</head>
<body>
    <h2>Audit Logs (Filter, Export, Pagination)</h2>

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
            <button type="submit" name="export" value="csv">Export CSV</button>
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
                <td><?= htmlspecialchars(substr($log['details'], 0, 40)) ?><?= strlen($log['details']) > 40 ? '...' : '' ?></td>
                <td><?= $log['created_at'] ?></td>
                <td><a href="#" class="view-details" 
                                    data-id="<?= $log['log_id'] ?>"
                                    data-user="<?= htmlspecialchars($log['username']) ?>"
                                    data-action="<?= htmlspecialchars($log['action']) ?>"
                                    data-details="<?= htmlspecialchars($log['details']) ?>"
                                    data-date="<?= $log['created_at'] ?>"
                                >👁 View</a></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

    <!-- Pagination Controls -->
       <?php if ($totalPages > 1): ?>
           <div class="pagination">
               <?php if ($page > 1): ?>
                   <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">⬅ Prev</a>
               <?php endif; ?>
               <span>Page <?= $page ?> of <?= $totalPages ?></span>
               <?php if ($page < $totalPages): ?>
                   <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next ➡</a>
               <?php endif; ?>
           </div>
       <?php endif; ?> 
        
    <p><a href="dashboard.php">⬅ Back to Dashboard</a></p>

    <!-- Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3>Log Details</h3>
            <p><strong>ID:</strong> <span id="modalId"></span></p>
            <p><strong>User:</strong> <span id="modalUser"></span></p>
            <p><strong>Action:</strong> <span id="modalAction"></span></p>
            <p><strong>Details:</strong></p>
            <pre id="modalDetails" style="background:#f4f4f4; padding:10px; border-radius:5px;"></pre>
            <p><strong>Date:</strong> <span id="modalDate"></span></p>
        </div>
    </div> 
    
    <script>
        // Modal JS
        const modal = document.getElementById('detailsModal');
        const closeBtn = document.querySelector('.close-btn');
        const detailLinks = document.querySelectorAll('.view-details');
    
        detailLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                document.getElementById('modalId').textContent = link.dataset.id;
                document.getElementById('modalUser').textContent = link.dataset.user;
                document.getElementById('modalAction').textContent = link.dataset.action;
                document.getElementById('modalDetails').textContent = link.dataset.details;
                document.getElementById('modalDate').textContent = link.dataset.date;
                modal.style.display = 'block';
            });
        });
    
        closeBtn.onclick = () => modal.style.display = 'none';
        window.onclick = (e) => { if (e.target === modal) modal.style.display = 'none'; };
    </script>
</body>
</html>