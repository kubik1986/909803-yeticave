<?php
/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param mysqli $link  Ресурс соединения
 * @param string $sql SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function db_get_prepare_stmt($link, $sql, $data = []) {
    $stmt = mysqli_prepare($link, $sql);
    if ($data) {
        $types = '';
        $stmt_data = [];
        foreach ($data as $value) {
            $type = null;
            if (is_int($value)) {
                $type = 'i';
            }
            else if (is_string($value)) {
                $type = 's';
            }
            else if (is_double($value)) {
                $type = 'd';
            }
            if ($type) {
                $types .= $type;
                $stmt_data[] = $value;
            }
        }
        $values = array_merge([$stmt, $types], $stmt_data);
        $func = 'mysqli_stmt_bind_param';
        $func(...$values);
    }
    return $stmt;
}

/**
 * Создает подключение к сервру MySQL и возвращает идентификатор подключения
 *
 * @param array $db Массив с параметрами подключения
 * @return mysqli $link Идентификатор подключения к серверу MySQL
 */
function db_connect($db) {
    $link = mysqli_connect($db['host'], $db['user'], $db['password'], $db['database']);
    if ($link) {
        $sql = "SET time_zone = '" . $db['timezone'] . "'";
        $set_time_zone = mysqli_query($link, $sql);
    }
    if (!$link || !$set_time_zone) {
        exit('Извините, на сайте ведутся технические работы');
    }
    return $link;
}

/**
 * Возвращает массив категорий
 *
 * @param mysqli $link Идентификатор подключения к серверу MySQL
 * @return array Массив категорий
 */
function db_get_categories($link) {
    $result = [];
    $sql =
        "SELECT *
            FROM categories";
    if ($query = mysqli_query($link, $sql)) {
        $result = mysqli_fetch_all($query, MYSQLI_ASSOC);
    }
    else {
        exit('Произошла ошибка. Попробуйте снова или обратитесь к администратору.');
    }
    return $result;
}

/**
 * Возвращает массив данных для указанной категории
 *
 * @param mysqli $link Идентификатор подключения к серверу MySQL
 * @return array Массив данных указанной категории
 */
function db_get_category($link, $category_id) {
    $result = [];
    $sql =
        "SELECT *
            FROM categories
            WHERE category_id = $category_id";
    if ($query = mysqli_query($link, $sql)) {
        $result = mysqli_fetch_array($query, MYSQLI_ASSOC);
    }
    else {
        exit('Произошла ошибка. Попробуйте снова или обратитесь к администратору.');
    }
    return $result;
}

/**
 * Возвращает массив открытых лотов или количество открытых лотов
 *
 * @param mysqli $link Идентификатор подключения к серверу MySQL
 * @param int|bool $limit Количество лотов, отображаемое на странице
 * @param int|bool $category_id ID категории лота
 * @param int|bool $page_id ID страницы при постраничной навигации
 * @param bool $records_count Параметр, определяющий тип результата вычисления (false - массив лотов, true - количество лотов)
 * @return array|int Массив открытых лотов|количество открытых лотов
 */
function db_get_opened_lots($link, $limit, $category_id = false, $page_id = false, $records_count = false) {
    $result_array = [];
    $result_count = 0;
    $category_filter = !empty($category_id) ? 'AND c.category_id = ' . $category_id : '';
    $limit_filter = !empty($limit) ? 'LIMIT ' . $limit : '';
    $offset_filter = !empty($page_id) && !empty($limit) ? 'OFFSET ' . ($page_id - 1) * $limit : '';
    $sql =
        "SELECT lot_id, title, starting_price, img, COUNT(b.bet_id) AS bets_count, COALESCE(MAX(b.amount),starting_price) AS price, expiry_date, c.name AS category
            FROM lots l
            JOIN categories c USING (category_id)
            LEFT JOIN bets b USING (lot_id)
            WHERE l.expiry_date > NOW() $category_filter
            GROUP BY l.lot_id
            ORDER BY l.adding_date DESC
            $limit_filter
            $offset_filter";
    if ($query = mysqli_query($link, $sql)) {
        if ($records_count) {
            $result_count = mysqli_num_rows($query);
        }
        else {
            $result_array = mysqli_fetch_all($query, MYSQLI_ASSOC);
        }
    }
    else {
        exit('Произошла ошибка. Попробуйте снова или обратитесь к администратору.');
    }
    return $records_count ? $result_count : $result_array;
}

/**
 * Возвращает массив данных для указанного лота
 *
 * @param mysqli $link Идентификатор подключения к серверу MySQL
 * @param int $lot_id ID лота
 * @return array Массив данных указанного лота
 */
function db_get_lot($link, $lot_id) {
    $result = [];
    $sql =
        "SELECT l.*, c.name AS category, COALESCE((SELECT MAX(amount) FROM bets WHERE lot_id = $lot_id), starting_price) AS price
            FROM lots l
            JOIN categories c USING (category_id)
            WHERE lot_id = $lot_id";
    if ($query = mysqli_query($link, $sql)) {
        $result = mysqli_fetch_array($query, MYSQLI_ASSOC);
    }
    else {
        exit('Произошла ошибка. Попробуйте снова или обратитесь к администратору.');
    }
    return $result;
}

/**
 * Возвращает массив ставок для указанного лота
 *
 * @param mysqli $link Идентификатор подключения к серверу MySQL
 * @param int $lot_id ID лота
 * @return array Массив ставок для указанного лота
 */
function db_get_bets($link, $lot_id) {
    $result = [];
    $sql =
        "SELECT adding_date, amount, b.user_id, u.name AS user
          FROM bets b
          JOIN users u USING (user_id)
          WHERE lot_id = $lot_id
          ORDER BY adding_date DESC";
    if ($query = mysqli_query($link, $sql)) {
        $result = mysqli_fetch_all($query, MYSQLI_ASSOC);
    }
    else {
        exit('Произошла ошибка. Попробуйте снова или обратитесь к администратору.');
    }
    return $result;
}

/**
 * Выполняет запись новой строки в таблицу лотов базы данных на основе переданных данных и возвращает идентификатор этой строки
 *
 * @param mysqli $link Идентификатор подключения к серверу MySQL
 * @param array $data Массив данных для подготовленного выражения
 * @return string Идентификатор записанной строки
 */
function db_add_lot($link, $data) {
    $lot_id = '';
    $sql =
        "INSERT INTO lots (title, description, img, starting_price, expiry_date, bet_step, category_id, author_id)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = db_get_prepare_stmt($link, $sql, [
        $data['lot-name'],
        $data['message'],
        $data['file-name'],
        $data['lot-rate'],
        $data['lot-date'],
        $data['lot-step'],
        $data['category'],
        $data['author']
    ]);
    $result = mysqli_stmt_execute($stmt);
    if ($result) {
        $lot_id = mysqli_insert_id($link);
    }
    else {
        exit('Произошла ошибка. Попробуйте снова или обратитесь к администратору.');
    }
    return $lot_id;
}

/**
 * Выполняет запись новой строки в таблицу ставок базы данных на основе переданных данных и возвращает идентификатор этой строки
 *
 * @param mysqli $link Идентификатор подключения к серверу MySQL
 * @param array $data Массив данных для подготовленного выражения
 * @return string Идентификатор записанной строки
 */
function db_add_bet($link, $data) {
    $bet_id = '';
    $sql =
        "INSERT INTO bets (amount, user_id, lot_id)
          VALUES (?, ?, ?)";
    $stmt = db_get_prepare_stmt($link, $sql, [
        $data['cost'],
        $data['user_id'],
        $data['lot_id']
    ]);
    $result = mysqli_stmt_execute($stmt);
    if ($result) {
        $bet_id = mysqli_insert_id($link);
    }
    else {
        exit('Произошла ошибка. Попробуйте снова или обратитесь к администратору.');
    }
    return $bet_id;
}

/**
 * Определяет, существует ли запись в таблице пользователей, у которой значение поля email совпадает с указанным
 *
 * @param mysqli $link Идентификатор подключения к серверу MySQL
 * @param string $email E-mail адрес
 * @return bool true - запись с указанным e-mail найдена, false - запись не найдена
 */
function db_is_registered_email($link, $email) {
    $result = 0;
    $sql =
        "SELECT user_id
            FROM users
            WHERE email = '$email'";
    if ($query = mysqli_query($link, $sql)) {
        $result = mysqli_num_rows($query);
    }
    else {
        exit('Произошла ошибка. Попробуйте снова или обратитесь к администратору.');
    }
    return !($result === 0);
}
?>
