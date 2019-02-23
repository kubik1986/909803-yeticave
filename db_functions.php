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
 * Возвращает массив категорий или массив данных указанной категории
 *
 * @param mysqli $link Идентификатор подключения к серверу MySQL
 * @param array $where Ассоциативный массив вида [<имя_поля_таблицы_БД> => <значение_поля>], указывающий фильтр поиска
 * @return array Массив категорий или массив данных указанной категории
 */
function db_get_categories($link, $where=[]) {
    $result = [];
    $sql_where = '';
    if (!empty($where)) {
        $sql_where = "WHERE " . key($where) . "='" . current($where) . "'";
    }
    $sql =
        "SELECT *
            FROM categories
            $sql_where";
    if ($query = mysqli_query($link, $sql)) {
        $result = empty($where) ? mysqli_fetch_all($query, MYSQLI_ASSOC) : mysqli_fetch_array($query, MYSQLI_ASSOC);
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
 * Возвращает массив открытых лотов или количество открытых лотов как результат полнотекстового поиска
 *
 * @param mysqli $link Идентификатор подключения к серверу MySQL
 * @param int|bool $limit Количество лотов, отображаемое на странице
 * @param string $search_text Текст поискового запроса
 * @param int|bool $category_id ID категории лота
 * @param int|bool $page_id ID страницы при постраничной навигации
 * @param bool $records_count Параметр, определяющий тип результата вычисления (false - массив лотов, true - количество лотов)
 * @return array|int Массив открытых лотов|количество открытых лотов
 */
function db_search_opened_lots($link, $limit, $search_text, $category_id = false, $page_id = false, $records_count = false) {
    $result_array = [];
    $result_count = 0;
    $category_filter = empty($category_id) ? '' : 'c.category_id = ' . $category_id . ' AND';
    $limit_filter = !empty($limit) ? 'LIMIT ' . $limit : '';
    $offset_filter = !empty($page_id) && !empty($limit) ? 'OFFSET ' . ($page_id - 1) * $limit : '';
    $sql =
        "SELECT lot_id, title, starting_price, img, COUNT(b.bet_id) AS bets_count, COALESCE(MAX(b.amount),starting_price) AS price, expiry_date, c.name AS category
            FROM lots l
            JOIN categories c USING (category_id)
            LEFT JOIN bets b USING (lot_id)
            WHERE l.expiry_date > NOW() AND $category_filter MATCH (title,description) AGAINST ('$search_text')
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
 * Возвращает массив ставок, которые сделал указанный пользователь
 *
 * @param mysqli $link Идентификатор подключения к серверу MySQL
 * @param int $user_id ID пользователя
 * @return array Массив ставок указанного пользователя
 */
function db_get_user_bets($link, $user_id) {
    $result = [];
    $sql =
        "SELECT l.lot_id, l.title AS lot_title, c.name AS category, l.expiry_date AS lot_expiry_date, MAX(amount) AS amount, MAX(b.adding_date) AS adding_date, l.winner_id, l.img, l.author_id AS lot_author_id, u.contacts AS lot_author_contacts
            FROM bets b
            JOIN lots l USING (lot_id)
            JOIN users u USING (user_id)
            JOIN categories c USING (category_id)
            WHERE user_id = $user_id
            GROUP BY l.lot_id, l.title, c.name, l.expiry_date, l.winner_id, l.img, l.author_id, u.contacts
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
 * Выполняет запись новой строки в таблицу lots базы данных на основе переданных данных и возвращает идентификатор этой строки
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
 * Выполняет запись новой строки в таблицу bets базы данных на основе переданных данных и возвращает идентификатор этой строки
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
 * Определяет, существует ли запись в таблице users, у которой значение поля email совпадает с указанным
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

/**
 * Возвращает массив данных пользователя по указанному запросу
 *
 * @param mysqli $link Идентификатор подключения к серверу MySQL
 * @param string $search_field Имя поля таблицы users, по которому будет производиться поиск
 * @param string $value Значение поля
 * @return array Массив данных пользователя
 */
function db_get_user($link, $search_field, $value) {
    $result = [];
    $sql =
        "SELECT *
            FROM users
            WHERE $search_field = '$value'";
    if ($query = mysqli_query($link, $sql)) {
        $result = mysqli_fetch_array($query, MYSQLI_ASSOC);
    }
    else {
        exit('Произошла ошибка. Попробуйте снова или обратитесь к администратору.');
    }
    return $result;
}

/**
 * Выполняет запись новой строки в таблицу users базы данных на основе переданных данных и возвращает идентификатор этой строки
 *
 * @param mysqli $link Идентификатор подключения к серверу MySQL
 * @param array $data Массив данных для подготовленного выражения
 * @return string Идентификатор записанной строки
 */
function db_add_user($link, $data) {
    $user_id = '';
    $avatar_field = empty($data['file-name']) ? '' : ',avatar';
    $avatar_value = empty($data['file-name']) ? '' : ',?';
    $stmt_data = [
        $data['name'],
        $data['password'],
        $data['email'],
        $data['contacts']
    ];
    if (!empty($data['file-name'])) {
        array_push($stmt_data, $data['file-name']);
    }
    $sql =
        "INSERT INTO users (name, password, email, contacts $avatar_field)
            VALUES (?, ?, ?, ? $avatar_value)";
    $stmt = db_get_prepare_stmt($link, $sql, $stmt_data);
    $result = mysqli_stmt_execute($stmt);
    if ($result) {
        $user_id = mysqli_insert_id($link);
    }
    else {
        exit('Произошла ошибка. Попробуйте снова или обратитесь к администратору.');
    }
    return $user_id;
}
?>
