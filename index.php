<?php
require_once('init.php');

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
