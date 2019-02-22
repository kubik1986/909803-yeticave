<?php
require_once('init.php');

$max_bet_step = 999999; // Максимальный шаг ставки лота

// ID длота
$lot_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Лот по указанному ID
$lot = !empty($lot_id) ? db_get_lot($link, $lot_id) : [];

if (empty($lot)) {
    show_error('404', 'Данной страницы не существует на сайте.',  $init_data, $user, $categories);
    exit();
}

// Ставки по лоту
$bets = db_get_bets($link, $lot_id);

// Данные из формы
$data = [];

// Ошибки валидации
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($user) ||
    $user['user_id'] === $lot['author_id'] ||
    (!empty($bets) && $bets[0]['user_id'] === $user['user_id'])) {
        header("Location: lot.php?id=" . $lot_id);
        exit();
    }

    if (isset($_POST['cost']) && !empty(trim($_POST['cost']))) {
        $data['cost'] = trim($_POST['cost']);
    }
    else {
        $errors['cost'] = 'Это поле необходимо заполнить';
    }
    if (empty($errors['cost'])) {
        if (!ctype_digit($data['cost']) || $data['cost'] <= 0) {
            $errors['cost'] = 'Ставка должна быть целым положительным числом';
        }
        elseif ($data['cost'] < $lot['price'] + $lot['bet_step']) {
            $errors['cost'] = 'Ставка должна быть не меньше минимальной';
        }
        elseif ($data['cost'] - $lot['price'] > $max_bet_step) {
            $errors['cost'] = 'Ставка слишком высока. Максимальный шаг ставки - ' . $max_bet_step . ' р';
        }
    }

    if (empty($errors)) {
        $data['user_id'] = $user['user_id'];
        $data['lot_id'] = $lot_id;
        if (is_lot_closed($lot['expiry_date'])) {
            show_error('403', 'Вы не можете сделать ставку, так как аукцион по этому лоту завершен.',  $init_data, $user, $categories);
            exit();
        }
        $bet_id = db_add_bet($link, $data);

        // Обновление данных перед отрисовкой страницы
        $lot['price'] = $data['cost'];
        array_unshift($bets, [
            'adding_date' => date('Y-m-d H:i:s'),
            'amount' => $data['cost'],
            'user_id' => $user['user_id'],
            'user' => $user['name']
        ]);
    }
}

$page_content = include_template('lot.php', array_merge($init_data, [
    'errors' => $errors,
    'data' => $data,
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
