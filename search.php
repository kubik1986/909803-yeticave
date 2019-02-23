<?php
require_once('init.php');

// Количество лотов, выводимых на страницу
$lots_limit = 9;

// Массив данных для поиска
$search = [
    'text' => '',
    'category' => ''
];

if (!isset($_GET['search']) || empty(trim($_GET['search']))) {
    header("Location: /");
    exit();
}

// ID категории, в которой осуществляется поиск
$category_id = 0;

$search['text'] = trim($_GET['search']);
if (isset($_GET['category'])) {
    $category_id = intval($_GET['category']);
    $category = db_get_category($link, $category_id);
    if (!empty($category)) {
        $search['category'] = $category['name'];
        $init_data['current_category'] = $category;
    }
    else {
        header("Location: search.php?search=" . $search['text']);
        exit();
    }
}

// Количество открытых лотов по результатам поиска
$lots_count = db_search_opened_lots($link, false, $search['text'], $category_id, false, true);

// Число страниц для отображения лотов
$pages_count = (int) floor($lots_count / $lots_limit);
if ($lots_count === 0 || $lots_count % $lots_limit !== 0) {
    $pages_count++;
}

// ID страницы при постраничной навигации
$page_id = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page_id <= 0  || $page_id > $pages_count) {
    header("Location: search.php?category=" . $category_id . "&search=" . $search['text']);
    exit();
}

// Данные из строки запроса
$url_data = ['search' => $search['text']];
if (!empty($category_id)) {
    $url_data = array_merge(['category' => $category_id], $url_data);
}

// Данные для блока пагинации
$pagination_data = get_pagination_data($pages_count, $page_id, $url_data, 10);

// Лоты по результатам поиска в указанной категории и странице
$lots = db_search_opened_lots($link, $lots_limit, $search['text'], $category_id, $page_id);

$lots_list = include_template('_lots-list.php', array_merge($init_data, [
    'lots' => $lots
]));
$page_content = include_template('search.php', array_merge($init_data, [
    'search' => $search,
    'lots_list' => $lots_list,
    'pagination_data' => $pagination_data
]));
$layout_content = include_template('layout.php', array_merge($init_data, [
    'title' => 'Результаты поиска',
    'content' => $page_content,
    'user' => $user,
    'categories' => $categories
]));
print($layout_content);
?>
