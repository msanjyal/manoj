<?php
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$search = $_GET['search'] ?? '';
$genre = $_GET['genre'] ?? '';
$platform_filter = $_GET['platform_filter'] ?? '';
$completion_filter = $_GET['completion_filter'] ?? '';

$sql = "SELECT * FROM games WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ? OR developer LIKE ? OR publisher LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ssss";
}

if (!empty($genre)) {
    $sql .= " AND genre = ?";
    $params[] = $genre;
    $types .= "s";
}

if (!empty($platform_filter)) {
    $sql .= " AND platform LIKE ?";
    $params[] = "%$platform_filter%";
    $types .= "s";
}

if (!empty($completion_filter)) {
    $sql .= " AND completion_status = ?";
    $params[] = $completion_filter;
    $types .= "s";
}

$sql .= " ORDER BY game_id DESC";

if (!empty($params)) {
    $stmt = $mysqli->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $games = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $result = $mysqli->query($sql);
    $games = $result->fetch_all(MYSQLI_ASSOC);
}

$genres_result = $mysqli->query("SELECT DISTINCT genre FROM games WHERE genre IS NOT NULL ORDER BY genre");
$genres = $genres_result->fetch_all(MYSQLI_ASSOC);

$platforms_result = $mysqli->query("SELECT DISTINCT platform FROM games WHERE platform IS NOT NULL ORDER BY platform");
$platforms = $platforms_result->fetch_all(MYSQLI_ASSOC);

$completion_stats = $mysqli->query("SELECT completion_status, COUNT(*) as count FROM games GROUP BY completion_status");
$stats = $completion_stats->fetch_all(MYSQLI_ASSOC);

$total_games = count($games);
$total_playtime = $mysqli->query("SELECT SUM(playtime_hours) as total FROM games")->fetch_assoc()['total'] ?? 0;
$avg_rating = $mysqli->query("SELECT AVG(rating) as avg FROM games WHERE rating > 0")->fetch_assoc()['avg'] ?? 0;

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, ['cache' => false]);

echo $twig->render('index.twig', [
    'username' => $_SESSION['username'],
    'games' => $games,
    'genres' => $genres,
    'platforms' => $platforms,
    'stats' => $stats,
    'search' => $search,
    'genre_filter' => $genre,
    'platform_filter' => $platform_filter,
    'completion_filter' => $completion_filter,
    'total_games' => $total_games,
    'total_playtime' => $total_playtime,
    'avg_rating' => round($avg_rating, 1),
    'site_name' => SITE_NAME,
    'current_year' => date('Y'),
    'student_id' => STUDENT_ID,
    'author' => SITE_AUTHOR
]);
?>