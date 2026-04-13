<?php
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$json_input = file_get_contents('php://input');
if (empty($json_input)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No input data']);
    exit;
}

$input = json_decode($json_input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

$title = trim($input['title'] ?? '');
$genre = $input['genre'] ?? '';
$platform = $input['platform'] ?? '';
$min_rating = $input['min_rating'] ?? '';
$max_rating = $input['max_rating'] ?? '';
$min_price = $input['min_price'] ?? '';
$max_price = $input['max_price'] ?? '';
$completion_filter = $input['completion_filter'] ?? '';
$is_multiplayer = $input['is_multiplayer'] ?? '';
$has_dlc = $input['has_dlc'] ?? '';
$is_digital = $input['is_digital'] ?? '';
$sort_by = $input['sort_by'] ?? 'title_asc';

$sql = "SELECT * FROM games WHERE 1=1";
$params = [];
$types = "";

if (!empty($title)) {
    $sql .= " AND title LIKE ?";
    $params[] = "%$title%";
    $types .= "s";
}

if (!empty($genre)) {
    $sql .= " AND genre = ?";
    $params[] = $genre;
    $types .= "s";
}

if (!empty($platform)) {
    $sql .= " AND platform LIKE ?";
    $params[] = "%$platform%";
    $types .= "s";
}

if (!empty($min_rating) && is_numeric($min_rating)) {
    $sql .= " AND rating >= ?";
    $params[] = (float)$min_rating;
    $types .= "d";
}

if (!empty($max_rating) && is_numeric($max_rating)) {
    $sql .= " AND rating <= ?";
    $params[] = (float)$max_rating;
    $types .= "d";
}

if (!empty($min_price) && is_numeric($min_price)) {
    $sql .= " AND price >= ?";
    $params[] = (float)$min_price;
    $types .= "d";
}

if (!empty($max_price) && is_numeric($max_price)) {
    $sql .= " AND price <= ?";
    $params[] = (float)$max_price;
    $types .= "d";
}

if (!empty($completion_filter)) {
    $sql .= " AND completion_status = ?";
    $params[] = $completion_filter;
    $types .= "s";
}

if ($is_multiplayer === '1') {
    $sql .= " AND is_multiplayer = 1";
}

if ($has_dlc === '1') {
    $sql .= " AND has_dlc = 1";
}

if ($is_digital === '1') {
    $sql .= " AND is_digital = 1";
}

switch ($sort_by) {
    case 'title_desc':
        $sql .= " ORDER BY title DESC";
        break;
    case 'rating_asc':
        $sql .= " ORDER BY rating ASC";
        break;
    case 'rating_desc':
        $sql .= " ORDER BY rating DESC";
        break;
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    case 'playtime_desc':
        $sql .= " ORDER BY playtime_hours DESC";
        break;
    case 'release_desc':
        $sql .= " ORDER BY release_year DESC";
        break;
    default:
        $sql .= " ORDER BY title ASC";
        break;
}

$stmt = $mysqli->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $games = $result->fetch_all(MYSQLI_ASSOC);
        
        $safe_games = [];
        foreach ($games as $game) {
            $safe_games[] = [
                'game_id' => (int)$game['game_id'],
                'title' => htmlspecialchars($game['title'] ?? ''),
                'genre' => htmlspecialchars($game['genre'] ?? ''),
                'platform' => htmlspecialchars($game['platform'] ?? ''),
                'release_year' => $game['release_year'] ?? null,
                'developer' => htmlspecialchars($game['developer'] ?? ''),
                'publisher' => htmlspecialchars($game['publisher'] ?? ''),
                'playtime_hours' => $game['playtime_hours'] ?? null,
                'rating' => $game['rating'] ?? null,
                'completion_status' => $game['completion_status'] ?? 'Not Started',
                'price' => $game['price'] ?? null,
                'is_multiplayer' => (bool)($game['is_multiplayer'] ?? false),
                'has_dlc' => (bool)($game['has_dlc'] ?? false),
                'is_digital' => (bool)($game['is_digital'] ?? false)
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($safe_games);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Query execution failed: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Query preparation failed: ' . $mysqli->error]);
}
?>