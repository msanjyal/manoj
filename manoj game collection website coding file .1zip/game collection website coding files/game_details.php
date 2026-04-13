<?php
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$game_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$game_id) {
    header("Location: index.php");
    exit;
}

$stmt = $mysqli->prepare("SELECT * FROM games WHERE game_id = ?");
$stmt->bind_param("i", $game_id);
$stmt->execute();
$result = $stmt->get_result();
$game = $result->fetch_assoc();
$stmt->close();

if (!$game) {
    header("Location: index.php");
    exit;
}

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, ['cache' => false]);

echo $twig->render('game_details.twig', [
    'username' => $_SESSION['username'],
    'game' => $game,
    'site_name' => SITE_NAME,
    'current_year' => date('Y'),
    'student_id' => STUDENT_ID,
    'author' => SITE_AUTHOR
]);
?>