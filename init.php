<?php
require_once('config/config.php');
require_once('db_functions.php');
require_once('functions.php');

// Подключение к БД
require_once('config/db.php');
$link = db_connect($db);
mysqli_set_charset($link, "utf8");

// Установка временоой зоны
date_default_timezone_set($config['timezone']);

// Массив общих данных для передачи в функцию-шаблонизатор
$data = [
    'title' => $config['sitename'] . ' - интернет-аукцион сноубордического и горнолыжного снаряжения',
    'avatar_path' => $config['avatar_path'],
    'lot_img_path' => $config['lot_img_path']
];

// Флаг нахождения на главной странице
$is_main_page = ($_SERVER['REQUEST_URI'] === '/');

// Категории
$categories = db_get_categories($link);

// Пользователь
$user = [];
?>
