<?php
require_once 'config.php';
require_once 'db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $captcha_input = strtoupper(trim($_POST['captcha'] ?? ''));
    $captcha_stored = $_SESSION['captcha'] ?? '';
    
    if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
    if ($password !== $confirm) $errors[] = 'Passwords do not match';
    if ($captcha_input !== $captcha_stored) $errors[] = 'Invalid CAPTCHA code';
    
    if (empty($errors)) {
        $check = $mysqli->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $errors[] = 'Username or email already exists';
        }
        $check->close();
    }
    
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hash);
        
        if ($stmt->execute()) {
            $success = 'Registration successful! You can now login.';
            unset($_SESSION['captcha']);
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
        $stmt->close();
    }
}

$captcha_text = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6);
$_SESSION['captcha'] = $captcha_text;

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, ['cache' => false]);

echo $twig->render('register.twig', [
    'errors' => $errors,
    'success' => $success,
    'captcha' => $captcha_text,
    'site_name' => SITE_NAME,
    'current_year' => date('Y'),
    'student_id' => STUDENT_ID,
    'author' => SITE_AUTHOR
]);
?>