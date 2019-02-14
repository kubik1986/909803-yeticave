<?php
require_once('init.php');

// Количество лотов, выводимых на страницу
$lots_limit = 9;

// ID категории лота
$category_id = isset($_GET['category']) ? intval($_GET['category']) : false;
if ($category_id <= 0 || $category_id > count($categories)) {
    $category_id = 1;
}

// Количество открытых лотов в указанной категории
$lots_count = db_get_opened_lots($link, false, $category_id, false, true);

// Число страниц для отображения лотов
$pages_count = floor($lots_count / $lots_limit);
if ($lots_count % $lots_limit !== 0) {
    $pages_count++;
}

// ID страницы при постраничной навигации
$page_id = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page_id <= 0  || $page_id > $pages_count) {
    $page_id = 1;
}

// Данные для блока пагинации
$pagination_data = get_pagination_data($pages_count, $page_id, $_GET, 11);

// Лоты в указанной категории и странице
$lots = db_get_opened_lots($link, $lots_limit, $category_id, $page_id);

$lots_list = include_template('_lots-list.php', array_merge($data, [
    'lots' => $lots
]));
$page_content = include_template('all-lots.php', array_merge($data, [
    'categories' => $categories,
    'category_id' => $category_id,
    'lots_list' => $lots_list,
    'pagination_data' => $pagination_data
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
