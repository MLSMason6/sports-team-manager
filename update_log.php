<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    http_response_code(403);
    exit("Access denied");
}

$host = "localhost";
$dbname = "sports_manager";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['log_id']) && isset($_POST['details'])) {
        $logId = $_POST['log_id'];
        $newDetails = $_POST['details'];

        $stmt = $pdo->prepare("UPDATE AuditLog SET details = :details WHERE log_id = :log_id");
        $stmt->execute(['details' => $newDetails, 'log_id' => $logId]);

        // ✅ Log this action
        require_once "log_action.php";
        logAction($pdo, $_SESSION['user_id'], "Edited log", "Edited details for Log ID: $logId");

        echo "success";
    } else {
        echo "invalid";
    }

} catch (PDOException $e) {
    echo "error";
}