<?php
require_once('init.php');

$max_bet_step = 999999; // Максимальный шаг ставки лота

// ID длота
$lot_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Лот по указанному ID
$lot = !empty($lot_id) ? db_get_lot($link, $lot_id) : [];

if (empty($lot)) {
    $error = [
        'title' => '404 - Страница не найдена',
        'message' => 'Данной страницы не существует на сайте.'
    ];
    header("HTTP/1.0 404 Not Found");
    $page_content = include_template('error.php', ['error' => $error]);
    $layout_content = include_template('layout.php', array_merge($init_data, [
        'title' => $error['title'],
        'content' => $page_content,
        'user' => $user,
        'categories' => $categories
    ]));
    print($layout_content);
    exit();
}

// Ставки по лоту
$bets = db_get_bets($link, $lot_id);

// Данные из формы
$data = [];

// Ошибки валидации
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_error = ''; // Ошибка, если пользователь не атворизован, либо пользователь не имеет право делать ставку
    $user_error_header = 'HTTP/1.0 403 Forbidden';
    if (empty($user)) {
        $user_error_header = 'HTTP/1.0 401 Unauthorized';
        $user_error = 'Добавление ставок доступно только авторизованным пользователям. Пожалуйста, ввойдите в свой аккаунт, если у вас уже есть учетная запись, или зарегистрируйтесь.';
    }
    elseif ($user['user_id'] === $lot['author_id']) {
        $user_error = 'Вы не можете делать ставки по этому лоту, так как лот создан вами.';
    }
    elseif (!empty($bets) && $bets[0]['user_id'] === $user['user_id']) {
        $user_error = 'Вы не можете повторно cделать ставку по этому лоту.';
    }
    if(!empty($user_error)) {
        header($user_error_header);
        exit($user_error);
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
        if (!is_lot_closed($lot['expiry_date'])) {
            $bet_id = db_add_bet($link, $data);
            header("Location: /lot.php/?id=" . $lot_id);
            exit();
        }
        else {
            header("HTTP/1.0 403 Unauthorized");
            $user_error = [
                'title' => '403 - Доступ запрещен',
                'message' => 'Вы не можете сделать ставку, так как аукцион по этому лоту завершен.'
            ];
            $page_content = include_template('error.php', ['error' => $user_error]);
            $layout_content = include_template('layout.php', array_merge($init_data, [
                'title' => $user_error['title'],
                'content' => $page_content,
                'user' => $user,
                'categories' => $categories
            ]));
            print($layout_content);
            exit();
        }
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
