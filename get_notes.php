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

    if (isset($_GET['log_id'])) {
        $logId = $_GET['log_id'];

        $stmt = $pdo->prepare("SELECT n.note_text, n.created_at, u.username
                               FROM LogNotes n
                               JOIN Users u ON n.user_id = u.user_id
                               WHERE n.log_id = :log_id
                               ORDER BY n.created_at DESC");
        $stmt->execute(['log_id' => $logId]);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($notes);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}