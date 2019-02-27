<?php
require_once('init.php');

// Количество лотов, выводимых на страницу
$lots_limit = $config['search_page_lots_limit'];

// Массив данных для поиска
$search = [
    'text' => '',
    'category' => ''
];

if (!isset($_GET['search']) || empty(trim($_GET['search']))) {
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    }
    else {
        header("Location: /");
    }
    exit();
}

// ID категории, в которой осуществляется поиск
$category_id = 0;

$search['text'] = trim($_GET['search']);
if (isset($_GET['category'])) {
    $category_id = intval($_GET['category']);
    $category = db_get_categories($link, ['category_id' => $category_id]);
    if (!empty($category)) {
        $search['category'] = $category['name'];
        $init_data['current_category'] = $category;
    }
    else {
        $category_id = 0;
    }
}

// Количество открытых лотов по результатам поиска
$lots_count = db_get_opened_lots($link, false, mysqli_real_escape_string($link, $search['text']), $category_id, false, true);

// Число страниц для отображения лотов
$pages_count = (int) ceil($lots_count / $lots_limit);

// ID страницы при постраничной навигации
$page_id = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page_id <= 0  || $page_id > $pages_count) {
    $page_id = 1;
}

// Данные из строки запроса
$url_data = ['search' => $search['text']];
if (!empty($category_id)) {
    $url_data = array_merge(['category' => $category_id], $url_data);
}

// Данные для блока пагинации
$pagination_data = get_pagination_data($pages_count, $page_id, $url_data, 10);

// Лоты по результатам поиска в указанной категории и странице
$lots = db_get_opened_lots($link, $lots_limit, mysqli_real_escape_string($link, $search['text']), $category_id, $page_id);

$lots_list = include_template('_lots-list.php', array_merge($init_data, [
    'lots' => $lots,
    'not_found_message' => 'Ничего не найдено по вашему запросу.'
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
