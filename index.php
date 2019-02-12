<?php
require_once('init.php');

// Открытые лоты
$lots = get_items($link,
    "SELECT title, starting_price, img, COUNT(b.bet_id) AS bets_count, COALESCE(MAX(b.amount),starting_price) AS price, expiry_date, c.name AS category
    FROM lots l
    JOIN categories c USING (category_id)
    LEFT JOIN bets b USING (lot_id)
    WHERE l.expiry_date > NOW()
    GROUP BY l.lot_id
    ORDER BY l.adding_date DESC
    LIMIT 9");

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
