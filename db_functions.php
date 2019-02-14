<?php
/**
 * Создает подключение к сервру MySQL и возвращает идентификатор подключения
 *
 * @param array $db массив с параметрами подключения
 * @return mysqli $link идентификатор подключения к серверу MySQL
 */
function db_connect($db) {
    $link = mysqli_connect($db['host'], $db['user'], $db['password'], $db['database']);
    if (!$link) {
        exit('Извините, на сайте ведутся технические работы');
    }
    return $link;
}

/**
 * Возвращает массив категорий
 *
 * @param mysqli $link идентификатор подключения к серверу MySQL
 * @return array массив категорий
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
 * @param mysqli $link идентификатор подключения к серверу MySQL
 * @param int $limit количество лотов, отображаемое на странице
 * @param int $category_id ID категории лота
 * @param int $page_id ID страницы при постраничной навигации
 * @return array|int массив открытых лотов|количество открытых лотов
 */
function db_get_opened_lots($link, $limit, $category_id = false, $page_id = false, $records_count = false) {
    $result_array = [];
    $result_count = 0;
    $category_filter = '';
    if (!empty($category_id)) {
        $category_filter = 'AND c.category_id = ' . $category_id;
    }
    $offset_filter ='';
    if (!empty($page_id)) {
        $offset_filter = 'OFFSET ' . ($page_id - 1) * $limit;
    }
    $sql =
        "SELECT lot_id, title, starting_price, img, COUNT(b.bet_id) AS bets_count, COALESCE(MAX(b.amount),starting_price) AS price, expiry_date, c.name AS category
            FROM lots l
            JOIN categories c USING (category_id)
            LEFT JOIN bets b USING (lot_id)
            WHERE l.expiry_date > NOW() $category_filter
            GROUP BY l.lot_id
            ORDER BY l.adding_date DESC
            LIMIT $limit
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
?>
