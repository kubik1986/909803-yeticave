<?php
require_once('init.php');

// Открытые лоты
$lots=[];

// Запрос категорий
$sql =
    'SELECT name, class
    FROM categories';
$result = mysqli_query($link, $sql);
if ($result) {
    $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Запрос открытых лотов
$sql =
    'SELECT title, starting_price, img, COALESCE(MAX(b.amount),starting_price) AS price, expiry_date, c.name AS category
    FROM lots l
    JOIN categories c USING (category_id)
    LEFT JOIN bets b USING (lot_id)
    WHERE l.expiry_date > NOW()
    GROUP BY l.lot_id
    ORDER BY l.adding_date DESC
    LIMIT 9';
$result = mysqli_query($link, $sql);
if ($result) {
    $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

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
