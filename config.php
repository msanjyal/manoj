<?php
session_save_path('/tmp');
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('SITE_NAME', 'Game Collection System');
define('SITE_AUTHOR', 'Manoj Sanjel');
define('STUDENT_ID', '2449682');

require_once __DIR__ . '/vendor/autoload.php';
?>