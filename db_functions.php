<?php
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
 *
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
 *
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
?>
