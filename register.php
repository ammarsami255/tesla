<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'tesla';

header('Content-Type: application/json'); // Set JSON response header

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        $errors = [];

        // Validation
        if (empty($name)) {
            $errors[] = "Full name is required";
        }
        
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter and one number";
        }

        if ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match";
        }

        // Check if email exists
        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT id FROM login WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Email already registered";
            }
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO login (name, email, pass) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword]);
            
            echo json_encode(['success' => true, 'redirect' => 'login.html']);
            exit;
        } else {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'errors' => ["Database error: " . $e->getMessage()]]);
    exit;
}

echo json_encode(['success' => false, 'errors' => ["Invalid request"]]);