<?php
require_once('init.php');

if (empty($user)) {
    header("Location: login.php");
    exit();
}

$bets = db_get_user_bets($link, $user['user_id']);

$page_content = include_template('my-lots.php', array_merge($init_data, [
    'bets' => $bets,
    'user' => $user
]));
$layout_content = include_template('layout.php', array_merge($init_data, [
    'title' => 'Мои ставки',
    'content' => $page_content,
    'user' => $user,
    'categories' => $categories
]));
print($layout_content);
