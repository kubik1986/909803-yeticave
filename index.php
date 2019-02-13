<?php
require_once('init.php');

// Количество лотов, выводимых на страницу
$lots_limit = 9;

// ID категории лота
$category_id = false;

// Открытые лоты
$lots = db_get_opened_lots($link, $lots_limit);

$lots_list = include_template('_lots-list.php', array_merge($data, [
    'lots' => $lots
]));
$page_content = include_template('index.php', array_merge($data, [
    'categories' => $categories,
    'lots_list' => $lots_list
]));
$layout_content = include_template('layout.php', array_merge($data, [
    'content' => $page_content,
    'user' => $user,
    'categories' => $categories,
    'is_main_page' => $is_main_page,
    'category_id' => $category_id
]));
print($layout_content);
?>
