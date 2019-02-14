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
 * @param int|float  $price Цена лота
 * @return string Отформатированная строка цены
 */
function price_format($price) {
    $formated_price = ceil($price);
    $formated_price = number_format($formated_price, 0, ',', ' ');
    return $formated_price . '<b class="rub">р</b>';
}

/**
 * Форматирует количество ставок по лоту путем добавления наименования
 *
 * @param int $bets_count количество ставок
 * @return string Отформатированная строка количества ставок
 */
function bets_count_format($bets_count) {
    $end = '';
    $count = $bets_count % 100;
    if ($count > 19) {
        $count = $count % 10;
    }
    if ($count === 1) {
        $end = 'ка';
    }
    else if ($count >= 2 && $count <= 4) {
        $end = 'ки';
    }
    else {
        $end = 'ок';
    }
    return $bets_count . ' став' . $end;
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

function add_pagination_item(&$pagination_data, &$url_data, $index, $page_number, $current_page) {
    $page_href = '';  // строка href для ссылки
    $class = ' pagination-item-active';  // css-класс элемента списка
    if ($page_number !== $current_page) {
        $url_data['page'] = $page_number;
        $page_href = ' href="?' . http_build_query($url_data) . '"';
        $class = '';
    }
    $pagination_data[$index] = [
        'page_number' => $page_number,
        'class' => $class,
        'href' => $page_href
    ];
}

function get_pagination_data($pages_count, $current_page, $url_data, $max_items = 9) {
    if ($pages_count <= 1) {
        return [];
    }
    $pagination_data = [];
    $prev_href ='';  // строка href для ссылки "назад"
    $next_href ='';  // строка href для ссылки "вперед"
    if ($current_page > 1) {
        $url_data['page'] = $current_page - 1;
        $prev_href = ' href="?' . http_build_query($url_data) . '"';
    }
    if ($current_page < $pages_count) {
        $url_data['page'] = $current_page + 1;
        $next_href = ' href="?' . http_build_query($url_data) . '"';
    }
    $pagination_data[0] = ['page_number' => 'Назад', 'class' => ' pagination-item-prev', 'href' => $prev_href];

    if ($pages_count <= $max_items) {
        $i = 1;
        while ($i <= $pages_count) {
            $page_number = $i;  // текст ссылки на страницу (номер страницы)
            add_pagination_item($pagination_data, $url_data, $i, $page_number, $current_page);
            $i++;
        }
    }
    else {
        $left = $current_page - 1;  // количество страниц слева от текущей
        $right = $pages_count - $current_page;  // количество страниц справа от текущей
        $mid_pos = (int) ceil($max_items / 2);  // позиция центрального элемента списка
        add_pagination_item($pagination_data, $url_data, 1, 1, $current_page);

        if ($left > $mid_pos - 1 && $right > $mid_pos - 1) {
            $i = 2;
            while ($i <= $max_items - 1) {
                if ($i === 2 || $i === $max_items - 1) {
                    $pagination_data[$i] = ['page_number' => '...', 'class' => '', 'href' => ''];
                }
                else {
                    $page_number = $i + $current_page - $mid_pos;
                    add_pagination_item($pagination_data, $url_data, $i, $page_number, $current_page);
                }
                $i++;
            }
        }
        elseif ($left <= ($mid_pos - 1)) {
            $i = 2;
            while ($i <= $max_items - 1) {
                if ($i === $max_items - 1) {
                    $pagination_data[$i] = ['page_number' => '...', 'class' => '', 'href' => ''];
                }
                else {
                    $page_number = $i;
                    add_pagination_item($pagination_data, $url_data, $i, $page_number, $current_page);
                }
                $i++;
            }
        }
        else {
            $i = 2;
            while ($i <= $max_items - 1) {
                if ($i === 2) {
                    $pagination_data[$i] = ['page_number' => '...', 'class' => '', 'href' => ''];
                }
                else {
                    $page_number = $i + $pages_count - $max_items;
                    add_pagination_item($pagination_data, $url_data, $i, $page_number, $current_page);
                }
                $i++;
            }
        }
        add_pagination_item($pagination_data, $url_data, $max_items, $pages_count, $current_page);
    }

    $pagination_data[$max_items + 1] = ['page_number' => 'Вперед', 'class' => ' pagination-item-next', 'href' => $next_href];
    return $pagination_data;
}
?>
