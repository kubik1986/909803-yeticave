<?php
require_once('init.php');

if (empty($user)) {
    show_error('401', 'Добавление лотов доступно только авторизованным пользователям. Пожалуйста, войдите в свой аккаунт, если у вас уже есть учетная запись, или зарегистрируйтесь.',  $init_data, $user, $categories);
    exit();
}

$title_max_length = 100; // Максимальное кол-во символов в названии лота
$description_max_length = 750; // Максимальное кол-во символов в описании лота
$max_price = 99999999; // Максимальная стартовая цена лота
$max_bet_step = 999999; // Максимальный шаг ставки лота
$max_file_size = 1.5; // Максимальный размер загружаемого файла, МБ

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
            $errors[$key] = 'Это поле необходимо заполнить';
        }
    }

    if (empty($errors['lot-name']) && strlen($data['lot-name']) > $title_max_length) {
        $errors['lot-name'] = 'Наименование лота слишком длинное. Максимальное количество символов - ' . $title_max_length;
    }
    if (empty($errors['category']) && empty(db_get_categories($link, ['category_id' => mysqli_real_escape_string($link, $data['category'])]))) {
        $errors['category'] = 'Выберите категорию';
        $data['category'] = '';
    }
    if (empty($errors['message']) && strlen($data['message']) > $description_max_length) {
        $errors['message'] = 'Описание лота слишком длинное. Максимальное количество символов - ' . $description_max_length;
    }
    if (empty($errors['lot-rate'])) {
        $data['lot-rate'] = str_replace(',', '.', $data['lot-rate']);
        if (!is_numeric($data['lot-rate']) || $data['lot-rate'] <= 0) {
            $errors['lot-rate'] = 'Цена должна быть положительным числом';
        }
        elseif ($data['lot-rate'] > $max_price) {
            $errors['lot-rate'] = 'Цена слишком высокая. Максимальная цена - ' . $max_price . ' р';
        }
        else {
            $data['lot-rate'] = ceil($data['lot-rate']);
        }
    }
    if (empty($errors['lot-step'])) {
        if (!ctype_digit($data['lot-step']) || $data['lot-step'] <= 0) {
            $errors['lot-step'] = 'Шаг ставки должен быть целым положительным числом';
        }
        elseif ($data['lot-step'] > $max_bet_step) {
            $errors['lot-step'] = 'Шаг ставки слишком высок. Максимальный шаг - ' . $max_bet_step . ' р';
        }
    }
    if (empty($errors['lot-date'])) {
        $date = strtotime($data['lot-date']);
        $data['lot-date'] = !$date ? $data['lot-date'] : date('Y-m-d', $date);
        if (!$date) {
            $errors['lot-date'] = 'Дата некорректна';
        }
        elseif (time() >= $date) {
            $errors['lot-date'] = 'Дата окончания лота должна быть на 1 день больше текущей даты';
        }
    }

    if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
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
        $file_dir =  $init_data['lot_img_path'];
        move_uploaded_file($_FILES['photo']['tmp_name'], $file_dir . $file_name);
        $data['author'] = $user['user_id'];
        $data['file-name'] = $file_name;
        $lot_id = db_add_lot($link, $data);

        // Создание миниатюры изображения лота
        $lot_image = $file_dir . $file_name;
        $thumb_image =  $file_dir . 'tmb-' . $file_name;
        $thumb_width = 54;
        make_thumb($lot_image, $thumb_image, $thumb_width);

        header("Location: lot.php?id=" . $lot_id);
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
