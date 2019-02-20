<?php
require_once('init.php');

if(!empty($user)) {
    header("Location: /");
    exit();
}

$psw_min_length = 8;
$psw_max_length = 72;
$name_max_length = 100; // Максимальное кол-во символов в имени
$contacts_max_length = 255; // Максимальное кол-во символов в контактах
$max_file_size = 500; // Максимальный размер загружаемого файла, КБ

// Данные из формы
$data = [];

// Ошибки валидации
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $keys = ['email', 'password', 'name', 'message'];
    $file_name = '';

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
        elseif (db_is_registered_email($link, mysqli_real_escape_string($link, $data['email']))) {
            $errors['email'] = 'Пользователь с указанным e-mail уже зарегистрирован';
        }
    }
    if (empty($errors['password'])) {
        if (strlen($data['password']) < $psw_min_length) {
            $errors['password'] = 'Минимальная длина пароля - ' . $psw_min_length . ' символов';
        }
        if (strlen($data['password']) > $psw_max_length) {
            $errors['password'] = 'Максимальная длина пароля - ' . $psw_max_length . ' символов';
        }
        elseif (db_is_registered_email($link, $data['email'])) {
            $errors['email'] = 'Пользователь с указанным e-mail уже зарегистрирован';
        }
    }
    if (empty($errors['name']) && strlen($data['name']) > $name_max_length) {
        $errors['name'] = 'Имя слишком длинное. Максимальное количество символов - ' . $name_max_length;
    }
    if (empty($errors['message']) && strlen($data['message']) > $contacts_max_length) {
        $errors['message'] = 'Сообщение слишком длинное. Максимальное количество символов - ' . $contacts_max_length;
    }

    if (isset($_FILES['avatar']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
        $tmp_name = $_FILES['avatar']['tmp_name'];
        $file_size = $_FILES['avatar']['size'];
        $file_type = mime_content_type($tmp_name);
        if ($file_type !== 'image/png' && $file_type !== 'image/jpeg') {
            $errors['avatar'] = 'Неправильный тип файла. Загрузите файл в формате jpeg, jpg или png';
        }
        elseif ($file_size > $max_file_size * 1024) {
            $errors['avatar'] = 'Размер файла больше допустимого. Максимальный размер - ' . $max_file_size . ' КБ';
        }
        else {
            $file_extension = $file_type === 'image/jpeg' ? '.jpg' : '.png';
            $file_name = uniqid('user-') . $file_extension;
        }
    }

    if(empty($errors)) {
        move_uploaded_file($_FILES['avatar']['tmp_name'], __DIR__ . $init_data['avatar_path'] . $file_name);
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['file-name'] = $file_name;
        $user_id = db_add_user($link, $data);
        header("Location: login.php");
        exit();
    }
}

$page_content = include_template('sign-up.php', [
    'errors' => $errors,
    'data' => $data
]);
$layout_content = include_template('layout.php', array_merge($init_data, [
    'title' => 'Регистрация',
    'content' => $page_content,
    'user' => $user,
    'categories' => $categories
]));
print($layout_content);
?>
