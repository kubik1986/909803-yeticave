<?php
require_once('vendor/autoload.php');

$lots = db_get_closed_lots_without_winner($link);
if(!empty($lots)) {
    $transport = (new Swift_SmtpTransport('phpdemo.ru', 25))
        ->setUsername('keks@phpdemo.ru')
        ->setPassword('htmlacademy');
    $mailer = new Swift_Mailer($transport);

    foreach ($lots as $lot) {
        $bets = db_get_bets($link, $lot['lot_id']);
        $winner_id = $lot['author_id'];
        if (!empty($bets)) {
            $winner_id = $bets[0]['user_id'];
        }
        db_update_lot_winner($link, $lot['lot_id'], $winner_id);

        if ($winner_id !== $lot['author_id']) {
            $winner = db_get_users($link, ['user_id' => $winner_id]);

            $message = (new Swift_Message('Ваша ставка победила'))
                ->setFrom(['keks@phpdemo.ru' => 'YetiCave'])
                ->setTo([$winner['email'] => htmlspecialchars($winner['name'])]);
            $message_content = include_template('email.php', [
                'lot' => $lot,
                'winner' => $winner
            ]);
            $message->setBody($message_content, 'text/html');
            $result = $mailer->send($message);
        }
    }
}
?>
