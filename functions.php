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
        'ставка' => ['ставка', 'ставки', 'ставок'],
        'минута' => ['минута', 'минуты', 'минут'],
        'час' => ['час', 'часа', 'часов'],
        'рубль' => ['рубль', 'рубля', 'рублей']
    ];
    $result = '';
    if (!isset($words[$word])) {
        return result;
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
 * @param string $expiry_date Дата окончания торгов в формате ГГГГ-ММ-ДД
 * @return bool true - аукцион закончился, false - аукцион не закончился
 */
function is_lot_closed($expiry_date) {
    $current_date = date_create('now');
    $expiry_date = date_create_from_format('Y-m-d', $expiry_date);
    date_time_set($expiry_date, 0, 0);
    return $current_date >= $expiry_date;
}

/**
 * Определяет время до окончания торгов по лоту
 *
 * @param string $expiry_date Дата окончания торгов в формате ГГГГ-ММ-ДД
 * @return string Время до окончания торгов
 */
function get_lot_expiry_time($expiry_date) {
    $current_date = date_create('now');
    $expiry_date = date_create_from_format('Y-m-d', $expiry_date);
    date_time_set($expiry_date, 0, 0);
    if ($current_date >= $expiry_date) {
        return 'Торги окончены';
    }
    $diff = date_diff($expiry_date, $current_date);
    $days_to_expiry = intval(date_interval_format($diff, '%a'));
    if ($days_to_expiry === 0) {
        return date_interval_format($diff, '%H:%I');
    }
    elseif ($days_to_expiry <= 3) {
        return $days_to_expiry . ($days_to_expiry > 1 ? ' дня' : ' день');
    }
    return date_format($expiry_date, 'd.m.Y');
}

/**
 * Определяет, заканчивается ли время торгов по указанному лоту
 *
 * @param string $expiry_date Дата окончания торгов по лоту в формате ГГГГ-ММ-ДД
 * @param int $min_hours Количество часов (<= 24) до конца торгов, меньше которого аукцион считается заканчивающимся
 * @return bool true - аукцион заканчивается, false - аукцион не заканчивается
 */
function is_lot_finishing($expiry_date, $min_hours = 6) {
    $current_date = date_create('now');
    $expiry_date = date_create_from_format('Y-m-d', $expiry_date);
    date_time_set($expiry_date, 0, 0);
    $diff = date_diff($current_date, $expiry_date);
    $days_to_expiry = intval(date_interval_format($diff, '%a'));
    if ($days_to_expiry > 0) {
        return false;
    }
    $hours_to_expiry = intval(date_interval_format($diff, '%r%h'));
    return ($hours_to_expiry > 0 &&  $hours_to_expiry < $min_hours);
}

/**
 * Возвращает время, прошедшее с момента добавления ставки, или непосредственно время добавления ставки в удобочитаемом формате
 *
 * @param string $adding_date Время добавления ставки в формате ГГГГ-ММ-ДД ЧЧ:ММ:СС
 * @return string Отформатированное время добавления ставки
 */
function get_bet_add_time($adding_time) {
    $current_date = date_create('now');
    $adding_date = date_create($adding_time);
    $diff = date_diff($current_date, $adding_date);
    $days_passed = intval(date_interval_format($diff, '%a'));
    if ($days_passed === 0) {
        $hours_passed = intval(date_interval_format($diff, '%h'));
        if ($hours_passed === 0) {
            $minutes_passed = intval(date_interval_format($diff, '%i'));
            if ($minutes_passed === 0) {
                $seconds_passed = intval(date_interval_format($diff, '%s'));
                return $seconds_passed <= 30 ? 'Только что' : 'Минута назад';
            }
            return $minutes_passed === 1 ? 'Минута назад' : $minutes_passed . ' ' . num_format($minutes_passed, 'минута') . ' назад';
        }
        elseif ($hours_passed > 0 && $hours_passed <= 5) {
            return $hours_passed ===1 ? 'Час назад' : $hours_passed . ' ' . num_format($hours_passed, 'час') . ' назад';
        }
    }
    $current_date_midnight = date_create('today');
    $adding_date_midnight = date_create($adding_time);
    date_time_set($adding_date_midnight, 0, 0);
    $calendar_diff = date_diff($current_date_midnight, $adding_date_midnight);
    $calendar_days_passed = intval(date_interval_format($calendar_diff, '%a'));
    if ($calendar_days_passed <= 1) {
        return  $calendar_days_passed === 0 ? date_format($adding_date, 'Сегодня в H:i') : date_format($adding_date, 'Вчера в H:i');
    }
    return date_format($adding_date, 'd.m.y в H:i');
}

/**
 * Возвращает массив данных для блока пагинации
 *
 * @param int $pages_count Общее количество страниц
 * @param int $current_page Номер текущей страницы
 * @param array $url_data Массив исходных get-параметров страницы
 * @param int $max_items Максимальное количество страниц, отображаемое в списке
 * @return array Двумерный массив данных, каждый элемент которого содержит номер страницы, css-класс элемента списка и текст атрибута href
 */
function get_pagination_data($pages_count, $current_page, $url_data, $max_items = 9) {
    if ($pages_count <= 1) {
        return [];
    }
    $max_items = $pages_count < $max_items ? $pages_count : $max_items;
    $pagination_data = [];
    $prev_href ='';  // строка href для ссылки "назад"
    $next_href ='';  // строка href для ссылки "вперед"
    $left = $current_page - 1;  // количество страниц слева от текущей
    $right = $pages_count - $current_page;  // количество страниц справа от текущей
    $mid_pos = (int) ceil($max_items / 2);  // позиция центрального элемента списка
    $left_min = $mid_pos - 1; // количество элементов слева от центрального
    $right_min = $max_items - $mid_pos; // количество элементов справа от центрального
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
    while ($i <= $max_items) {
        $page_number = $i; // текущая страница в начале списка
        if ($left > $left_min && $right > $right_min) { // текущая страница где-то в середине списка
            $page_number = $i + $current_page - $mid_pos;
        }
        elseif ($right <= $right_min) { // текущая страница в конце списка
            $page_number = $i + $pages_count - $max_items;
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
    $pagination_data[$max_items + 1] = ['page_number' => 'Вперед', 'class' => ' pagination-item-next', 'href' => $next_href];
    return $pagination_data;
}
?>
