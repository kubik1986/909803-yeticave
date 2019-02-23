<?php
require_once('init.php');

// Количество лотов, выводимых на страницу
$lots_limit = 9;

// Категория
$category_id = 1;
$current_category = [];
if (isset($_GET['category'])) {
    $category_id = intval($_GET['category']);
}
else {
    header("Location: all-lots.php?category=1");
    exit();
}
$current_category = db_get_category($link, $category_id);
if (empty($current_category)) {
    header("Location: all-lots.php?category=1");
    exit();
}
$init_data['current_category'] = $current_category;

// Количество открытых лотов в указанной категории
$lots_count = db_get_opened_lots($link, false, $category_id, false, true);

// Число страниц для отображения лотов
$pages_count = (int) floor($lots_count / $lots_limit);
if ($lots_count === 0 || $lots_count % $lots_limit !== 0) {
    $pages_count++;
}

// ID страницы при постраничной навигации
$page_id = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page_id <= 0  || $page_id > $pages_count) {
    header("Location: all-lots.php?category=" . $category_id);
    exit();
}

// Данные для блока пагинации
$pagination_data = get_pagination_data($pages_count, $page_id, ['category' =>  $category_id], 10);

// Лоты в указанной категории и странице
$lots = db_get_opened_lots($link, $lots_limit, $category_id, $page_id);

$lots_list = include_template('_lots-list.php', array_merge($init_data, [
    'lots' => $lots
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
?>
