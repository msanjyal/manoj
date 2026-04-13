<?php
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $platform = trim($_POST['platform'] ?? '');
    $release_year = $_POST['release_year'] ?? null;
    $developer = trim($_POST['developer'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $playtime_hours = $_POST['playtime_hours'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $completion_status = $_POST['completion_status'] ?? 'Not Started';
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? null;
    $purchase_date = $_POST['purchase_date'] ?? null;
    $is_multiplayer = isset($_POST['is_multiplayer']) ? 1 : 0;
    $has_dlc = isset($_POST['has_dlc']) ? 1 : 0;
    $is_digital = isset($_POST['is_digital']) ? 1 : 0;
    
    if (empty($title)) $errors[] = 'Game title is required';
    if ($release_year && !is_numeric($release_year)) $errors[] = 'Release year must be a number';
    if ($rating && ($rating < 0 || $rating > 10)) $errors[] = 'Rating must be between 0 and 10';
    if ($playtime_hours && $playtime_hours < 0) $errors[] = 'Playtime cannot be negative';
    if ($price && $price < 0) $errors[] = 'Price cannot be negative';
    
    if (empty($errors)) {
        $playtime_hours = ($playtime_hours === '' || $playtime_hours === null) ? null : (float)$playtime_hours;
        $rating = ($rating === '' || $rating === null) ? null : (float)$rating;
        $price = ($price === '' || $price === null) ? null : (float)$price;
        $release_year = ($release_year === '' || $release_year === null) ? null : (int)$release_year;
        
        if ($purchase_date === '') {
            $purchase_date = null;
        }
        
        $stmt = $mysqli->prepare("
            INSERT INTO games 
            (title, genre, platform, release_year, developer, publisher, playtime_hours, rating, completion_status, description, price, purchase_date, is_multiplayer, has_dlc, is_digital) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "sssisssdssdsiii",
            $title,
            $genre,
            $platform,
            $release_year,
            $developer,
            $publisher,
            $playtime_hours,
            $rating,
            $completion_status,
            $description,
            $price,
            $purchase_date,
            $is_multiplayer,
            $has_dlc,
            $is_digital
        );
        
        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $errors[] = 'Failed to add game: ' . $stmt->error;
        }
        
        $stmt->close();
    }
}

$genres_result = $mysqli->query("SELECT DISTINCT genre FROM games WHERE genre IS NOT NULL ORDER BY genre");
$existing_genres = $genres_result->fetch_all(MYSQLI_ASSOC);

$platforms_result = $mysqli->query("SELECT DISTINCT platform FROM games WHERE platform IS NOT NULL ORDER BY platform");
$existing_platforms = $platforms_result->fetch_all(MYSQLI_ASSOC);

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, ['cache' => false]);

echo $twig->render('add_game.twig', [
    'username' => $_SESSION['username'],
    'errors' => $errors,
    'existing_genres' => $existing_genres,
    'existing_platforms' => $existing_platforms,
    'site_name' => SITE_NAME,
    'current_year' => date('Y'),
    'student_id' => STUDENT_ID,
    'author' => SITE_AUTHOR
]);
?>