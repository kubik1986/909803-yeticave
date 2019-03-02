<?php
require_once('init.php');
require_once('getwinner.php');

$init_data['is_main_page'] = true;

// Количество лотов, выводимых на страницу
$lots_limit = $config['main_page_lots_limit'];

// Количество открытых лотов
$lots_count = db_get_opened_lots($link, false, false, false, false, true);

// Число страниц для отображения лотов
$pages_count = (int) ceil($lots_count / $lots_limit);

// ID страницы при постраничной навигации
$page_id = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page_id <= 0  || $page_id > $pages_count) {
    $page_id = 1;
}
if (isset($_GET['page'])) {
    $init_data['is_main_page'] = false;
}

// Данные для блока пагинации
$pagination_data = get_pagination_data($pages_count, $page_id, [], 10);

// Открытые лоты на текущей странице
$lots = db_get_opened_lots($link, $lots_limit, false, false, $page_id);

$lots_list = include_template('_lots-list.php', array_merge($init_data, [
    'lots' => $lots,
    'not_found_message' => 'Активные лоты не найдены.'
]));
$page_content = include_template('index.php', array_merge($init_data, [
    'categories' => $categories,
    'lots_list' => $lots_list,
    'pagination_data' => $pagination_data
]));
$layout_content = include_template('layout.php', array_merge($init_data, [
    'content' => $page_content,
    'user' => $user,
    'categories' => $categories
]));
print($layout_content);
