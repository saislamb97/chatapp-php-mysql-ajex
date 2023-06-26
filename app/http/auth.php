<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Simple form validation
    if (empty($username)) {
        redirectToIndexWithError('Username is required');
    } elseif (empty($password)) {
        redirectToIndexWithError('Password is required');
    } else {
        // Database connection file
        require_once '../db.conn.php';

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['user_id'] = $user['user_id'];

            redirectToHome();
        } else {
            redirectToIndexWithError('Incorrect username or password');
        }
    }
} else {
    redirectToIndex();
}

function redirectToIndexWithError($errorMessage) {
    $location = '../../index.php?error=' . urlencode($errorMessage);
    header("Location: $location");
    exit;
}

function redirectToIndex() {
    header("Location: ../../index.php");
    exit;
}

function redirectToHome() {
    header("Location: ../../home.php");
    exit;
}
