<?php 
$host = "localhost";
$dbname = "sports_manager";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $newUser = "admin";
    $plainPassword = "password123";
    $role = "admin";

    // Hash password
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO Users (username, password, role) VALUES (:username, :password, :role)");
    $stmt->execute([
        'username' => $newUser,
        'password' => $hashedPassword,
        'role' => $role
    ]);

    echo "User created successfully with hashed password!";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}