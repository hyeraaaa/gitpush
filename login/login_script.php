<?php
session_start();
require_once 'dbh.inc.php';

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user = null;
    $user_type = null;

    // Regular expression for validating an email
    $emailPattern = '/^[\w.%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,6}$/';

    // Validate email format
    if (!preg_match($emailPattern, $email)) {
        header("Location: login.php?error=Invalid email format");
        exit();
    }

    // Check if the user is an admin
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user) {
        $user_type = 'admin';
    } else {
        // Check if the user is a student
        $stmt = $pdo->prepare("SELECT * FROM student WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch();
        if ($user) {
            $user_type = 'student';
        }
    }

    if ($user) {
        if (password_verify($password, $user['password'])) {
            // Store user info and user type in session
            $_SESSION['user'] = $user;
            $_SESSION['user_type'] = $user_type;
            if ($user_type === 'admin') {
                header("Location: ../admin/admin.php");
                exit();
            } else {
                header("Location: ../user/user.php");
                exit();
            }
        } else {
            error_log("Invalid password for user: $email");
            header("Location: login.php?error=Invalid credentials");
            exit();
        }
    } else {
        error_log("No user found with email: $email");
        header("Location: login.php?error=Invalid credentials");
        exit();
    }
} else {
    header("Location: login.php?error=Email and password are required");
    exit();
}
