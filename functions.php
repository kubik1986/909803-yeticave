<?php
/**
 * Подключает шаблон HTML-разметки в сценарий на основе переданных данных
 *
 * @param string $name Имя файла шаблона
 * @param array $data Данные для вставки на место переменных в шаблоне
 * @return string HTML-разметка блока/основного контента/лэйаута
 */
function include_template($name, $data) {
    $name = 'templates/' . $name;
    $result = '';
    if (!is_readable($name)) {
        return $result;
    }
    ob_start();
    extract($data);
    require $name;
    $result = ob_get_clean();
    return $result;
}

/**
 * Форматирует цену с разбиением разрядов пробелом и добавлением знака рубля
 *
 * @param int|float $price Цена лота
 * @param bool $ruble_sign параметр, определяющий добавление знака рубля (true - знак рубля добавляется, false - выводится только числовое значение)
 * @return string Отформатированная строка цены
 */
function price_format($price, $ruble_sign = true) {
    $formated_price = ceil($price);
    $formated_price = number_format($formated_price, 0, ',', ' ');
    return $ruble_sign ? $formated_price . '<b class="rub">р</b>' : $formated_price;
}

/**
 * Форматирует числовое значение путем добавления наименования в правильном падеже
 *
 * @param int $num Число
 * @param string $word Наименование (существительное) для склонения из предопределенного массива
 * @return string Наименование в правильном падеже
 */
function num_format($num, $word) {
    $words = [
        'bet' => ['ставка', 'ставки', 'ставок'],
        'minute' => ['минута', 'минуты', 'минут'],
        'hour' => ['час', 'часа', 'часов'],
        'day' => ['день', 'дня', 'дней'],
        'ruble' => ['рубль', 'рубля', 'рублей']
    ];
    $result = '';
    if (!isset($words[$word])) {
        return $result;
    }
    $count = $num % 100;
    if ($count > 19) {
        $count = $count % 10;
    }
    if ($count === 1) {
        $result = $words[$word][0];
    }
    else if ($count >= 2 && $count <= 4) {
        $result = $words[$word][1];
    }
    else {
        $result = $words[$word][2];
    }
    return $result;
}

/**
 * Определяет, закончился ли аукцион по лоту
 *
 * @param string $expiry_date Дата окончания торгов
 * @return bool true - аукцион закончился, false - аукцион не закончился
 */
function is_lot_closed($expiry_date) {
    return time() >= strtotime($expiry_date);
}

/**
 * Определяет время до окончания торгов по лоту
 *
 * @param string $expiry_date Дата окончания торгов
 * @return string Время до окончания торгов
 */
function get_lot_expiry_time($expiry_date) {
    $expiry_time = strtotime($expiry_date);
    $seconds_to_expiry = $expiry_time - time();
    $days_to_expiry = (int) floor($seconds_to_expiry / 86400);
    $hours_to_expiry = (int) floor(($seconds_to_expiry % 86400) / 3600);
    $minutes_to_expiry = (int) floor(($seconds_to_expiry % 3600) / 60);
    $result = date('d.m.Y', $expiry_time);
    if ($seconds_to_expiry <= 0) {
        $result = 'Торги окончены';
    }
    elseif ($days_to_expiry === 0) {
        $result = sprintf('%02d:%02d', $hours_to_expiry, $minutes_to_expiry);
    }
    elseif ($days_to_expiry <= 3) {
        $result = sprintf('%d %s', $days_to_expiry, num_format($days_to_expiry, 'day'));
    }
    return $result;
}

/**
 * Возвращает CSS-класс таймера лота по указанной дате окончания аукциона
 *
 * @param string $expiry_date Дата окончания торгов по лоту
 * @param int $min_hours Количество часов (<= 24) до конца торгов, меньше которого аукцион считается заканчивающимся
 * @return string CSS-класс таймера
 */
function get_lots_timer_class($expiry_date, $min_hours = 6) {
    $lots_timer_class = '';
    $expiry_time = strtotime($expiry_date);
    $seconds_to_expiry = $expiry_time - time();
    $total_hours_to_expiry = (int) floor($seconds_to_expiry / 3600);
    if (time() >= $expiry_time) {
        $lots_timer_class = ' timer--end';
    }
    else if ($total_hours_to_expiry > 0 && $total_hours_to_expiry < $min_hours) {
        $lots_timer_class = ' timer--finishing';
    }
    return $lots_timer_class;
}

/**
 * Возвращает CSS-класс элемента (строки) таблицы ставок пользователя
 *
 * @param array $bet Массив данных ставки
 * @param array $user Массив данных пользователя
 * @return string CSS-класс строки таблицы ставок
 */
function get_rates_item_class($bet, $user) {
    $rate_item_class = '';
    if ($bet['winner_id'] === $user['user_id']) {
        $rate_item_class = ' rates__item--win';
    }
    elseif (is_lot_closed($bet['lot_expiry_date'])) {
        $rate_item_class = ' rates__item--end';
    }
    return $rate_item_class;
}

/**
 * Возвращает время, прошедшее с момента добавления ставки, или непосредственно время добавления ставки в удобочитаемом формате
 *
 * @param string $adding_time Дата и время добавления ставки
 * @return string Отформатированное время добавления ставки
 */
function get_bet_add_time($adding_time) {
    $add_time = strtotime($adding_time);
    $result = date('d.m.y в H:i', $add_time);
    if ($add_time > time()) {
        return 'Ошибка! Время больше текущего';
    }
    $seconds_passed = time() - $add_time;
    $days_passed = (int) floor($seconds_passed / 86400);
    $hours_passed = (int) floor(($seconds_passed % 86400) / 3600);
    $minutes_passed = (int) floor(($seconds_passed % 3600) / 60);
    if ($add_time >= strtotime('yesterday')) {
        $result = sprintf('Вчера в %s', date('H:i', $add_time));
    }
    if ($add_time >= strtotime('today')) {
        $result = sprintf('Сегодня в %s', date('H:i', $add_time));
    }
    if ($days_passed === 0) {
        if ($hours_passed === 0 && $minutes_passed === 0) {
            $result = $seconds_passed <= 30 ? 'Только что' : 'Минута назад';
        }
        elseif ($hours_passed === 0) {
            $result = $minutes_passed === 1 ? 'Минута назад' : sprintf('%d %s назад', $minutes_passed, num_format($minutes_passed, 'minute'));
        }
        elseif ($hours_passed > 0 && $hours_passed <= 10) {
            $result = $hours_passed === 1 ? 'Час назад' : sprintf('%d %s назад', $hours_passed, num_format($hours_passed, 'hour'));
        }
    }
    return $result;
}

/**
 * Возвращает массив данных для блока пагинации
 *
 * @param int $pages_count Общее количество страниц
 * @param int $current_page Номер текущей страницы
 * @param array $url_data Массив исходных get-параметров страницы
 * @param int $max_pages Максимальное количество страниц, отображаемое в списке
 * @return array Двумерный массив данных, каждый элемент которого содержит номер страницы, css-класс элемента списка и текст атрибута href
 */
function get_pagination_data($pages_count, $current_page, $url_data, $max_pages = 9) {
    if ($pages_count <= 1) {
        return [];
    }
    $max_pages = $pages_count < $max_pages ? $pages_count : $max_pages;
    $pagination_data = [];
    $prev_href ='';  // строка href для ссылки "назад"
    $next_href ='';  // строка href для ссылки "вперед"
    $left = $current_page - 1;  // количество страниц слева от текущей
    $right = $pages_count - $current_page;  // количество страниц справа от текущей
    $mid_pos = (int) ceil($max_pages / 2);  // позиция центрального элемента списка
    $left_min = $mid_pos - 1; // количество элементов слева от центрального
    $right_min = $max_pages - $mid_pos; // количество элементов справа от центрального
    if ($current_page > 1) {
        $url_data['page'] = $current_page - 1;
        $prev_href = ' href="?' . http_build_query($url_data) . '"';
    }
    if ($current_page < $pages_count) {
        $url_data['page'] = $current_page + 1;
        $next_href = ' href="?' . http_build_query($url_data) . '"';
    }
    $pagination_data[0] = ['page_number' => 'Назад', 'class' => ' pagination-item-prev', 'href' => $prev_href];
    $i = 1;
    while ($i <= $max_pages) {
        $page_number = $i; // текущая страница в начале списка
        if ($left > $left_min && $right > $right_min) { // текущая страница где-то в середине списка
            $page_number = $i + $current_page - $mid_pos;
        }
        elseif ($right <= $right_min) { // текущая страница в конце списка
            $page_number = $i + $pages_count - $max_pages;
        }
        $page_href = '';
        $class = ' pagination-item-active';
        if ($page_number !== $current_page) {
            $url_data['page'] = $page_number;
            $page_href = ' href="?' . http_build_query($url_data) . '"';
            $class = '';
        }
        $pagination_data[$i] = [
            'page_number' => $page_number,
            'class' => $class,
            'href' => $page_href
        ];
        $i++;
    }
    $pagination_data[$max_pages + 1] = ['page_number' => 'Вперед', 'class' => ' pagination-item-next', 'href' => $next_href];
    return $pagination_data;
}

/**
 * Создает миниатюру изображения
 *
 * @param string $src Полный путь к файлу исходного изображения
 * @param string $dest Полный путь к файлу целевого изображения
 * @param int thumb_width Ширина целевого изображения в px
 * @return bool true - миниатюра создана, false - миниатюра не создана.
 */
function make_thumb($src, $dest, $thumb_width) {
    $result = false;
    $gd_module = 'php_gd2.dll';
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        $gd_module = 'gd2.os';
    }
    if (extension_loaded('gd') || (!extension_loaded('gd') && dl($gd_module))) {
        $file_type = mime_content_type($src);
        $source_image = $file_type === 'image/jpeg' ? imagecreatefromjpeg($src) : imagecreatefrompng($src);
        $width = imagesx($source_image);
        $height = imagesy($source_image);

        $create = 'imagecreatetruecolor';
        $copy = 'imagecopyresampled';
        if (!function_exists('imagecreatetruecolor')) {
            $create = 'imagecreate';
            $copy = 'imagecopyresized';
        }
        $thumb_height = floor($height * ($thumb_width / $width));
        $virtual_image = $create($thumb_width, $thumb_height);
        if ($file_type === 'image/png') {
            imageAlphaBlending($virtual_image, false);
            imageSaveAlpha($virtual_image, true);
        }

        $copy($virtual_image, $source_image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
        $file_type === 'image/jpeg' ? imagejpeg($virtual_image, $dest, 100) : imagepng($virtual_image, $dest);
        imagedestroy($virtual_image);
        if (file_exists($dest)) {
            $result = true;

        }
    }
    return $result;
}

/**
 * Отрисовывает страницу ошибки по указанным http-коду и тексту ошибки
 *
 * @param string $http_code Код состояния http
 * @param string $message Текст сообщения об ошибке
 * @param array $init_data Массив данных из init.php для заполнения шаблона
 * @param array $user Массив данных пользователя для заполнения шаблона
 * @param array $categories Массив категорий для заполнения шаблона
 * @return void
 */
function show_error($http_code, $message, $init_data, $user, $categories) {
    $http_codes = [
        '401' => ['title' => '401 - Требуется авторизация',
                  'header' => 'HTTP/1.1 401 Unauthorized'],
        '403' => ['title' => '403 - Доступ запрещен',
                  'header' => 'HTTP/1.1 403 Forbidden'],
        '404' => ['title' => '404 - Страница не найдена',
                  'header' => 'HTTP/1.1 404  Not Found']
    ];
    $page_title = isset($http_codes[$http_code]) ? $http_codes[$http_code]['title'] : $http_codes['404']['title'];
    $header = isset($http_codes[$http_code]) ? $http_codes[$http_code]['header'] : $http_codes['404']['header'];
    $error = [
        'title' => $page_title,
        'message' => $message
    ];
    header($header);
    $page_content = include_template('error.php', ['error' => $error]);
    $layout_content = include_template('layout.php', array_merge($init_data, [
        'title' => $error['title'],
        'content' => $page_content,
        'user' => $user,
        'categories' => $categories
    ]));
    print($layout_content);
}
?>
