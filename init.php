<?php
require_once('config.php');
require_once('functions.php');
require_once('data.php');

date_default_timezone_set($config['timezone']);

$data = [
    'title' => $config['sitename'] . ' - интернет-аукцион сноубордического и горнолыжного снаряжения',
    'avatar_path' => $config['avatar_path'],
    'lot_img_path' => $config['lot_img_path']
];
?>
