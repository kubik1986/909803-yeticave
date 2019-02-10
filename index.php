<?php
require_once('init.php');

$page_content = include_template('index.php', array_merge($data, [
    'categories' => $categories,
    'lots' => $lots
]));
$layout_content = include_template('layout.php', array_merge($data, [
    'content' => $page_content,
    'user' => $user,
    'categories' => $categories
]));
print($layout_content);
?>
