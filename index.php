<?php
require_once('init.php');

// Количество лотов, выводимых на страницу
$lots_limit = 9;

// ID категории лота
$category_id = false;
if (isset($_GET['category'])) {
    $category_id = intval($_GET['category']);
}

// ID страницы при постраничной навигации
$page_id = false;
if (isset($_GET['page'])) {
    $page_id = intval($_GET['page']);
}

// Открытые лоты
$lots = db_get_opened_lots($link, $lots_limit, $category_id, $page_id);

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
