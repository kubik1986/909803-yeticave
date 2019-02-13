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

// Число страниц для отображения лотов
$pages_count = floor(count($lots) / $lots_limit);
if (count($lots) % $lots_limit !== 0) {
    $pages_count++;
}

$lots_list = include_template('_lots-list.php', array_merge($data, [
    'lots' => $lots
]));
$page_content = include_template('all-lots.php', array_merge($data, [
    'categories' => $categories,
    'category_id' => $category_id,
    'lots_list' => $lots_list
]));
$layout_content = include_template('layout.php', array_merge($data, [
    'title' => 'Все лоты в категории «' . $categories[$category_id - 1]['name'] . '»',
    'content' => $page_content,
    'user' => $user,
    'categories' => $categories,
    'is_main_page' => $is_main_page,
    'category_id' => $category_id
]));
print($layout_content);
?>
