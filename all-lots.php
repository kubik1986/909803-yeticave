<?php
require_once('init.php');

// Количество лотов, выводимых на страницу
$lots_limit = $config['all_lots_page_lots_limit'];

// Категория
$category_id = 1;
$current_category = [];
if (isset($_GET['category'])) {
    $category_id = intval($_GET['category']);
}
$current_category = db_get_categories($link, ['category_id' => $category_id]);
if (empty($current_category)) {
    $category_id = 1;
    $current_category = db_get_categories($link, ['category_id' => $category_id]);
}
$init_data['current_category'] = $current_category;

// Количество открытых лотов в указанной категории
$lots_count = db_get_opened_lots($link, false, false, $category_id, false, true);

// Число страниц для отображения лотов
$pages_count = (int) ceil($lots_count / $lots_limit);

// ID страницы при постраничной навигации
$page_id = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page_id <= 0  || $page_id > $pages_count) {
    $page_id = 1;
}

// Данные для блока пагинации
$pagination_data = get_pagination_data($pages_count, $page_id, ['category' =>  $category_id], 10);

// Лоты в указанной категории и странице
$lots = db_get_opened_lots($link, $lots_limit, false, $category_id, $page_id);

$lots_list = include_template('_lots-list.php', array_merge($init_data, [
    'lots' => $lots,
    'not_found_message' => 'Активные лоты не найдены.'
]));
$page_content = include_template('all-lots.php', array_merge($init_data, [
    'lots_list' => $lots_list,
    'pagination_data' => $pagination_data
]));
$layout_content = include_template('layout.php', array_merge($init_data, [
    'title' => 'Все лоты в категории «' . $current_category['name'] . '»',
    'content' => $page_content,
    'user' => $user,
    'categories' => $categories
]));
print($layout_content);
