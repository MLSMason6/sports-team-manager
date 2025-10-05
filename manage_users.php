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

    // Handle delete
    if (isset($_GET['delete'])) { 
        $userId = $_GET['delete'];

        // Prevent deleting yourself 
        if ($userId == $_SESSION['user_id']) { 
             echo "<p style='color:red;'>You cannot delete your own account.</p>";
        } else {
            $stmt = $pdo->prepare("DELETE FROM Users WHERE user_id = :id");
            $stmt->execute(['id' => $userId]);
            echo "<p style='color:red;'>User deleted successfully!</p>";
        }
    }

    // Handle role update 
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_role'])) { 
        $userId = $_POST['user_id'];
        $newRole = $_POST['role'];

        // Prevent changing your own role 
        if ($userId == $_SESSION['user_id']) { 
            echo "<p style='color:red;'>You cannot change your own role.</p>";
        } else { 
            $stmt = $pdo->prepare("UPDATE Users SET role = :role WHERE user_id = :id");
            $stmt->execute(['role' => $newRole, 'id' => $userId]);
            echo "<p style='color:green;'>User role updated successfully!</p>";

            require_once "log_action.php";
            logAction($pdo, $_SESSION['user_id'], "Updated role", 
                "User ID: $userId → New Role: $newRole");
        }
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
            <form method="POST">
                <td><?= $user['user_id'] ?></td>
                <td><?= $user['username'] ?></td>
                <td>
                    <select name="role" <?= ($user['user_id'] == $_SESSION['user_id']) ? "disabled" : "" ?>>
                        <option value="coach" <?= $user['role'] == 'coach' ? 'selected' : '' ?>>Coach</option>
                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="superadmin" <?= $user['role'] == 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
                    </select>
                </td>
                <td><?= $user['created_at'] ?></td>
                <td>
                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                        <button type="submit" name="update_role">Update Role</button>
                        <a href="manage_users.php?delete=<?= $user['user_id'] ?>" onclick="return confirm('Delete this user?')">Delete</a>
                    <?php else: ?>
                        (You)
                    <?php endif; ?>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><a href="register.php">➕ Register New User</a></p>
    <p><a href="dashboard.php">⬅ Back to Dashboard</a></p>
</body>
</html>