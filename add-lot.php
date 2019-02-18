<?php
require_once('init.php');

if(empty($user)) {
    header("HTTP/1.0 401 Unauthorized");
    $error = [
        'title' => '401 - Требуется авторизация',
        'message' => 'Добавление лотов доступно только авторизованным пользователям. Пожалуйста, ввойдите в свой аккаунт, если у вас уже есть учетная запись, или зарегистрируйтесь.'
    ];
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

$title_max_length = 100; // Максимальное кол-во символов в названии лота
$description_max_length = 750; // Максимальное кол-во символов в описании лота
$max_price = 99999999; // Максимальная стартовая цена лота
$max_bet_step = 999999; // Максимальный шаг ставки лота
$max_file_size = 1.5; // Максимальная размер загружаемого файла, МБ

// Данные из формы
$data = [];

// Ошибки валидации
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $keys = ['lot-name', 'category', 'message', 'lot-rate', 'lot-step', 'lot-date'];
    $file_name = '';

    foreach ($keys as $key) {
        if (isset($_POST[$key]) && !empty(trim($_POST[$key]))) {
            $data[$key] = trim($_POST[$key]);
        }
        else {
            if ($key !== 'category') {
                $errors[$key] = 'Это поле необходимо заполнить';
            }
            else {
                $errors[$key] = 'Выберите категорию';
            }
        }
    }

    foreach ($data as $key => $value) {
        if ($key === 'lot-name') {
            if (strlen($value) > $title_max_length) {
                $errors[$key] = 'Наименование лота слишком длинное. Максимальное количество символов - ' . $title_max_length;
            }
            continue;
        }
        if ($key === 'category') {
            $value = intval($value);
            if ($value <= 0 || $value > count($categories)) {
                $errors[$key] = 'Выберите категорию';
                $data[$key] = '';
            }
            continue;
        }
        if ($key === 'message') {
            if (strlen($value) > $description_max_length) {
                $errors[$key] = 'Описание лота слишком длинное. Максимальное количество символов - ' . $description_max_length;
            }
            continue;
        }
        if ($key === 'lot-rate') {
            $value = str_replace(',', '.', $value);
            $data[$key] = $value;
            if (!is_numeric($value) || $value <= 0) {
                $errors[$key] = 'Цена должна быть положительным числом';
            }
            elseif ($value > $max_price) {
                $errors[$key] = 'Цена слишком высокая. Максимальная цена - ' . $max_price . ' р';
            }
            else {
                $data[$key] = ceil($value);
            }
            continue;
        }
        if ($key === 'lot-step') {
            if (!ctype_digit($value) || $value <= 0) {
                $errors[$key] = 'Шаг ставки должен быть целым положительным числом';
            }
            elseif ($value > $max_bet_step) {
                $errors[$key] = 'Шаг ставки слишком высок. Максимальный шаг - ' . $max_bet_step . ' р';
            }
            continue;
        }
        if ($key === 'lot-date') {
            $is_correct_iso_format = preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $value);
            $is_correct_local_format = preg_match('/[0-9]{2}\.[0-9]{2}\.[0-9]{4}/', $value);
            if (!$is_correct_iso_format && !$is_correct_local_format) {
                $errors[$key] = 'Дата должна быть в формате ДД.ММ.ГГГГ';
            }
            else {
                $date_separate = $is_correct_iso_format ? explode('-', $value) : explode('.', $value);
                $day = $is_correct_iso_format ? $date_separate[2] : $date_separate[0];
                $month = $date_separate[1];
                $year = $is_correct_iso_format ? $date_separate[0] : $date_separate[2];
                if (!checkdate($month, $day, $year)) {
                    $errors[$key] = 'Дата некорректна';
                }
                else {
                    if ($is_correct_local_format) {
                        $data[$key] = implode('-', array_reverse($date_separate));
                    }
                    $expiry_date = date_create($data[$key]);
                    date_time_set($expiry_date, 0, 0);
                    if (date_create('now') >= $expiry_date) {
                        $errors[$key] = 'Дата окончания лота должна быть на 1 день больше текущей даты';
                    }
                }
            }
        }
    }

    if (isset($_FILES['photo']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
        $tmp_name = $_FILES['photo']['tmp_name'];
        $file_size = $_FILES['photo']['size'];
        $file_type = mime_content_type($tmp_name);
        if ($file_type !== 'image/png' && $file_type !== 'image/jpeg') {
            $errors['photo'] = 'Неправильный тип файла. Загрузите файл в формате jpeg, jpg или png';
        }
        elseif ($file_size > $max_file_size * 1024 * 1024) {
            $errors['photo'] = 'Размер файла больше допустимого. Максимальный размер - ' . $max_file_size . ' МБ';
        }
        else {
            $file_extension = $file_type === 'image/jpeg' ? '.jpg' : '.png';
            $file_name = uniqid('lot-' . $user['user_id'] . '-') . $file_extension;
        }
    }
    else {
        $errors['photo'] = 'Загрузите файл с изображением лота';
    }

    if(empty($errors)) {
        move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__ . $init_data['lot_img_path'] . $file_name);
        $data['author'] = $user['user_id'];
        $data['file-name'] = $file_name;
        $lot_id = db_add_lot($link, $data);
        header("Location: lot.php/?id=" . $lot_id);
        exit();
    }
}

$page_content = include_template('add-lot.php', [
    'errors' => $errors,
    'data' => $data,
    'categories' => $categories
]);
$layout_content = include_template('layout.php', array_merge($init_data, [
    'title' => 'Добавление лота',
    'content' => $page_content,
    'user' => $user,
    'categories' => $categories
]));
print($layout_content);
?>
