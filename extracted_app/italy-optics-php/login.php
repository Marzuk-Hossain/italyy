<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $actionType = $_POST['action_type'] ?? 'login';

    $db = getDB();

    if (!$db) {
        $_SESSION['flash_error'] = "Database connection error. Please try again.";
        header("Location: index.php");
        exit;
    }

    // --- REGISTRATION / SIGNUP HANDLER ---
    if ($actionType === 'register') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name)) {
            $_SESSION['flash_error'] = "Please provide your full name.";
            header("Location: index.php");
            exit;
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = "Please provide a valid email address.";
            header("Location: index.php");
            exit;
        }

        if (empty($password) || strlen($password) < 4) {
            $_SESSION['flash_error'] = "Password must be at least 4 characters long.";
            header("Location: index.php");
            exit;
        }

        // Check if email already registered
        $stmt = $db->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['flash_error'] = "This email address is already registered. Please log in with your password.";
            header("Location: index.php");
            exit;
        }

        // Create new customer account with secure password hash
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $db->prepare("INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, 'customer')");
        $insert->execute([$name, $email, $phone, $hash]);
        $userId = $db->lastInsertId();

        $_SESSION['user'] = [
            'id' => (int)$userId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => 'customer'
        ];

        $_SESSION['flash_success'] = "Welcome to Italy Optics, {$name}! Your account has been created successfully.";
        header("Location: index.php");
        exit;
    }

    // --- LOGIN HANDLER (CUSTOMER & ADMIN) ---
    if (empty($email) || empty($password)) {
        $_SESSION['flash_error'] = "Please enter both your email address and password.";
        header("Location: index.php");
        exit;
    }

    // Fetch user by email
    $stmt = $db->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?)");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['flash_error'] = "Authentication failed: No account found with the email '{$email}'. Please check your spelling or sign up.";
        header("Location: index.php");
        exit;
    }

    // Strict Password Verification
    if (!password_verify($password, $user['password_hash'])) {
        $_SESSION['flash_error'] = "Authentication failed: Incorrect password for '{$email}'. Access denied.";
        header("Location: index.php");
        exit;
    }

    // Password verified! Create session
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'phone' => $user['phone'] ?? '',
        'role' => $user['role']
    ];

    if ($user['role'] === 'admin') {
        $_SESSION['flash_success'] = "Admin authentication verified. Welcome, {$user['name']}!";
        header("Location: admin/index.php");
    } else {
        $_SESSION['flash_success'] = "Welcome back, {$user['name']}! You are logged in.";
        header("Location: index.php");
    }
    exit;
}

header("Location: index.php");
exit;
