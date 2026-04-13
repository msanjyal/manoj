<?php
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$genres_result = $mysqli->query("SELECT DISTINCT genre FROM games WHERE genre IS NOT NULL ORDER BY genre");
$genres = $genres_result->fetch_all(MYSQLI_ASSOC);

$platforms_result = $mysqli->query("SELECT DISTINCT platform FROM games WHERE platform IS NOT NULL ORDER BY platform");
$platforms = $platforms_result->fetch_all(MYSQLI_ASSOC);

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, ['cache' => false]);

echo $twig->render('search.twig', [
    'username' => $_SESSION['username'],
    'genres' => $genres,
    'platforms' => $platforms,
    'site_name' => SITE_NAME,
    'current_year' => date('Y'),
    'student_id' => STUDENT_ID,
    'author' => SITE_AUTHOR
]);
?>