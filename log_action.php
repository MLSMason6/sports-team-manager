<?php 
function logAction ($pdo, $userId, $action, $details = null) { 
    $stmt = $pdo->prepare("INSERT INTO AuditLog (user_id, action, details) VALUES (:user_id, :action, :details)");
    $stmt->execute([
        'user_id' => $userId,
        'action' => $action,
        'details' => $details
    ]);
}
?>