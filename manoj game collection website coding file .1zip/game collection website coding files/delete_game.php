<?php
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$game_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($game_id) {
    $stmt = $mysqli->prepare("DELETE FROM games WHERE game_id = ?");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: index.php");
exit;
?>