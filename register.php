<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] !== 'superadmin') {
    die("Access denied. Only Super Admins can register new users.");
}
$host = "localhost";
$dbname = "sports_manager";
$username = "root";
$password = "";

try { 
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $message = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") { 
        $newUser = $_POST['username'];
        $plainPassword = $_POST['password'];
        $role = $_POST['role'];

        // Check if username already exists 
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = :username");
        $stmt->execute(['username' => $newUser]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) { 
            $message = "<p style='color:red;'>Username already taken.</p>";
        } else { 
            // Hash password 
            $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

            // Insert new user
             $stmt = $pdo->prepare("INSERT INTO Users (username, password, role) 
                                   VALUES (:username, :password, :role)");
            $stmt->execute([
                'username' => $newUser,
                'password' => $hashedPassword,
                'role' => $role
            ]);

             $message = "<p style='color:green;'>User registered successfully!</p>";
        }
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>Register New User</h2>
    <?= $message ?>
    <form method="POST">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Role:</label><br>
        <select name="role">
            <option value="coach">Coach</option>
            <option value="admin">Admin</option>
        </select><br><br>

        <button type = "submit">Register</button>
    </form>

    <p><a href="login.php">Back to Login</a></p>
</body>
</html>