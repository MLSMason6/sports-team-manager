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

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['log_id']) && isset($_POST['note'])) {
        $logId = $_POST['log_id'];
        $note = trim($_POST['note']);
        $userId = $_SESSION['user_id'];

        if ($note === "") {
            exit("empty");
        }

        $stmt = $pdo->prepare("INSERT INTO LogNotes (log_id, user_id, note_text) VALUES (:log_id, :user_id, :note)");
        $stmt->execute(['log_id' => $logId, 'user_id' => $userId, 'note' => $note]);

        // Log this action in the audit log
        require_once "log_action.php";
        logAction($pdo, $userId, "Added note", "Added note to Log ID: $logId");

        echo "success";
    } else {
        echo "invalid";
    }
} catch (PDOException $e) {
    echo "error";
}