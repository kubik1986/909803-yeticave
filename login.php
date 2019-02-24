<?php
require_once('init.php');

if(!empty($user)) {
    header("Location: /");
    exit();
}

// Данные из формы
$data = [];

// Ошибки валидации
$errors = [];

// Ошибка аутентификации
$is_auth_error = false;

$user_data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $keys = ['email', 'password'];

    foreach ($keys as $key) {
        if (isset($_POST[$key]) && !empty(trim($_POST[$key]))) {
            $data[$key] = trim($_POST[$key]);
        }
        else {
            $errors[$key] = 'Это поле необходимо заполнить';
        }
    }

    if (empty($errors['email'])) {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный формат адреса электронной почты';
        }
        else {
            $user_data = db_get_users($link, ['email' => mysqli_real_escape_string($link, $data['email'])]);
        }
    }
    if (empty($errors['password'])) {
        if (!empty($user_data) && password_verify($data['password'], $user_data['password'])) {
            $user['user_id'] = $user_data['user_id'];
            $user['name'] = $user_data['name'];
            $user['avatar'] = $user_data['avatar'];
        }
        elseif (empty($errors['email'])) {
            $is_auth_error = true;
        }
    }

    if(empty($errors) && !$is_auth_error) {
        $_SESSION['user'] = $user;
        header("Location: /");
        exit();
    }
}

$page_content = include_template('login.php', [
    'errors' => $errors,
    'data' => $data,
    'is_auth_error' => $is_auth_error
]);
$layout_content = include_template('layout.php', array_merge($init_data, [
    'title' => 'Вход',
    'content' => $page_content,
    'user' => $user,
    'categories' => $categories
]));
print($layout_content);
?>
