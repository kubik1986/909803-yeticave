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
?>
