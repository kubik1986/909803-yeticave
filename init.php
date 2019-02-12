<?php
require_once('config/config.php');
require_once('functions.php');

// Подключение к БД
require_once('config/db.php');
$link = mysqli_connect($db['host'], $db['user'], $db['password'], $db['database']);
if (!$link) {
    exit('Извините, на сайте ведутся технические работы');
}
mysqli_set_charset($link, "utf8");

// Установка временоой зоны
date_default_timezone_set($config['timezone']);

// Массив общих данных для передачи в фунцию-шаблонизатор
$data = [
    'title' => $config['sitename'] . ' - интернет-аукцион сноубордического и горнолыжного снаряжения',
    'avatar_path' => $config['avatar_path'],
    'lot_img_path' => $config['lot_img_path']
];

// Категории
$categories = get_items($link,
    "SELECT *
    FROM categories");

// Пользователь
$user = [];
?>
