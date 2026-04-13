<?php
session_start();

if (isset($_GET['generate'])) {
    $text = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6);
    $_SESSION['captcha'] = $text;
    
    $image = imagecreate(150, 50);
    $bg = imagecolorallocate($image, 26, 35, 126);
    $text_color = imagecolorallocate($image, 0, 188, 212);
    
    for ($i = 0; $i < 5; $i++) {
        $line_color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        imageline($image, 0, rand() % 50, 150, rand() % 50, $line_color);
    }
    
    for ($i = 0; $i < 100; $i++) {
        $pixel_color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        imagesetpixel($image, rand() % 150, rand() % 50, $pixel_color);
    }
    
    $font = 5;
    $x = 30;
    $y = 18;
    
    for ($i = 0; $i < strlen($text); $i++) {
        $char_color = imagecolorallocate($image, rand(200, 255), rand(200, 255), rand(200, 255));
        imagestring($image, $font, $x, $y + rand(-5, 5), $text[$i], $char_color);
        $x += 20;
    }
    
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
    exit;
}
?>