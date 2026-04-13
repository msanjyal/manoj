<?php
$host = 'localhost';
$username = '2449682';
$password = 'Manoj@123';
$database = 'db2449682';

$mysqli = new mysqli($host, $username, $password, $database);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");

$check_table = $mysqli->query("SHOW TABLES LIKE 'users'");
if ($check_table->num_rows == 0) {
    $create_users = "
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$mysqli->query($create_users)) {
        die("Failed to create users table: " . $mysqli->error);
    }
    
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $mysqli->query("INSERT INTO users (username, email, password_hash) VALUES ('admin', 'admin@games.com', '$hash')");
}

$check_games = $mysqli->query("SHOW TABLES LIKE 'games'");
if ($check_games->num_rows == 0) {
    $create_games = "
    CREATE TABLE games (
        game_id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        genre VARCHAR(100),
        platform VARCHAR(100),
        release_year INT,
        developer VARCHAR(255),
        publisher VARCHAR(255),
        playtime_hours DECIMAL(10,1),
        rating DECIMAL(3,1),
        completion_status ENUM('Not Started', 'In Progress', 'Completed', 'Dropped') DEFAULT 'Not Started',
        description TEXT,
        price DECIMAL(10,2),
        purchase_date DATE,
        is_multiplayer BOOLEAN DEFAULT 0,
        has_dlc BOOLEAN DEFAULT 0,
        is_digital BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (!$mysqli->query($create_games)) {
        die("Failed to create games table: " . $mysqli->error);
    }
    
    $sample_games = "
    INSERT INTO games (title, genre, platform, release_year, developer, publisher, playtime_hours, rating, completion_status, description, price, is_multiplayer, has_dlc, is_digital) VALUES
    ('The Witcher 3: Wild Hunt', 'RPG', 'PC, PS4, Xbox One', 2015, 'CD Projekt Red', 'CD Projekt', 120.5, 9.7, 'Completed', 'Epic fantasy RPG with rich storytelling', 39.99, 0, 1, 1),
    ('God of War', 'Action-Adventure', 'PS4, PS5', 2018, 'Santa Monica Studio', 'Sony', 45.2, 9.8, 'Completed', 'Norse mythology action game', 49.99, 0, 0, 1),
    ('Minecraft', 'Sandbox', 'Multi-platform', 2011, 'Mojang Studios', 'Microsoft', 500.0, 9.0, 'In Progress', 'Creative building sandbox game', 26.95, 1, 1, 0),
    ('Elden Ring', 'Action RPG', 'PC, PS4, PS5, Xbox', 2022, 'FromSoftware', 'Bandai Namco', 95.3, 9.6, 'In Progress', 'Open-world action RPG', 59.99, 1, 1, 1),
    ('Hades', 'Roguelike', 'Multi-platform', 2020, 'Supergiant Games', 'Supergiant Games', 65.5, 9.5, 'Completed', 'Mythology-inspired roguelike', 24.99, 0, 0, 1);
    ";
    
    $mysqli->multi_query($sample_games);
    while ($mysqli->more_results()) {
        $mysqli->next_result();
    }
}

// Check and create indexes only if they don't exist
$index_result = $mysqli->query("SHOW INDEX FROM games WHERE Key_name = 'idx_genre'");
if ($index_result->num_rows == 0) {
    $mysqli->query("CREATE INDEX idx_genre ON games(genre)");
}

$index_result = $mysqli->query("SHOW INDEX FROM games WHERE Key_name = 'idx_platform'");
if ($index_result->num_rows == 0) {
    $mysqli->query("CREATE INDEX idx_platform ON games(platform)");
}

$index_result = $mysqli->query("SHOW INDEX FROM games WHERE Key_name = 'idx_rating'");
if ($index_result->num_rows == 0) {
    $mysqli->query("CREATE INDEX idx_rating ON games(rating)");
}
?>