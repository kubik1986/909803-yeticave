<?php
require_once('init.php');

// ID длота
$lot_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Лот по указанному ID
$lot = !empty($lot_id) ? db_get_lot($link, $lot_id) : [];

if (empty($lot)) {
    header("HTTP/1.0 404 Not Found");
    $page_content = include_template('404.php', []);
    $layout_content = include_template('layout.php', array_merge($init_data, [
        'title' => '404 - Страница не найдена',
        'content' => $page_content,
        'user' => $user,
        'categories' => $categories
    ]));
    print($layout_content);
    exit();
}

// Ставки по лоту
$bets = db_get_bets($link, $lot_id);

$page_content = include_template('lot.php', array_merge($init_data, [
    'lot' => $lot,
    'bets' => $bets,
    'user' => $user
]));
$layout_content = include_template('layout.php', array_merge($init_data, [
    'title' => $lot['title'],
    'content' => $page_content,
    'user' => $user,
    'categories' => $categories
]));
print($layout_content);
?>
