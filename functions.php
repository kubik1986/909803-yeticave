<?php
date_default_timezone_set($config['timezone']);

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

function price_format($price) {
    $formated_price = ceil($price);
    $formated_price = number_format($formated_price, 0, ',', ' ');
    return $formated_price . '<b class="rub">р</b>';
}

function get_lot_expiry_time($expiry_date) {
    $current_date = date_create('now');
    $expiry_date = date_create_from_format('d.m.Y', $expiry_date);
    date_time_set($expiry_date, 0, 0);
    if ($current_date >= $expiry_date) {
        return '00:00';
    }
    $diff = date_diff($expiry_date, $current_date);
    $days_to_expiry = (int) date_interval_format($diff, '%a');
    if ($days_to_expiry === 0) {
        return date_interval_format($diff, '%H:%I');
    }
    elseif ($days_to_expiry <= 3) {
        return $days_to_expiry . ($days_to_expiry > 1 ? ' дня' : ' день');
    }
    return date_format($expiry_date, 'd.m.Y');
}
?>
