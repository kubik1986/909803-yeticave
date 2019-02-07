<?php
require_once('config.php');
require_once('functions.php');
require_once('data.php');

$page_content = include_template('index.php', [
    'categories' => $categories,
    'lots' => $lots
]);
$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'title' => 'Главная',
    'user' => $user,
    'categories' => $categories
]);
print($layout_content);
?>
