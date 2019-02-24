<?php
require_once('init.php');

$lots = db_get_closed_lots_without_winner($link);
if(!empty($lots)) {
    foreach ($lots as $lot) {
        $bets = db_get_bets($link, $lot['lot_id']);
        $winner_id = $lot['author_id'];
        if (!empty($bets)) {
            $winner_id = $bets[0]['user_id'];
        }
        db_update_lot_winner($link, $lot['lot_id'], $winner_id);

        if ($winner_id !== $lot['author_id']) {
            $winner = db_get_users($link, ['user_id' => $winner_id]);

        }
    }
}
?>
