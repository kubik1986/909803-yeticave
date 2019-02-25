<section class="rates container">
    <h2>Мои ставки</h2>
    <?php if (empty($bets)): ?>
    <p>Вы еще не делали ни одной ставки.</p>
    <?php else: ?>
    <table class="rates__list">
        <?php foreach ($bets as $bet): ?>
        <tr class="rates__item<?=get_rates_item_class($bet, $user); ?>">
            <td class="rates__info">
                <div class="rates__img">
                    <img src="<?=file_exists($lot_img_path . 'tmb-' . $bet['img']) ? $lot_img_path . 'tmb-' . $bet['img'] : $lot_img_path . $bet['img']; ?>" width="54" height="40" alt="Изображение лота">
                </div>
                <?php if ($bet['winner_id'] === $user['user_id']): ?>
                <div>
                    <h3 class="rates__title"><a href="lot.php?id=<?=$bet['lot_id']; ?>"><?=htmlspecialchars($bet['lot_title']); ?></a></h3>
                    <p><?=htmlspecialchars($bet['lot_author_contacts']); ?></p>
                </div>
                <?php else: ?>
                <h3 class="rates__title"><a href="lot.php?id=<?=$bet['lot_id']; ?>"><?=htmlspecialchars($bet['lot_title']); ?></a></h3>
                <?php endif; ?>
            </td>
            <td class="rates__category">
                <?=$bet['category']; ?>
            </td>
            <td class="rates__timer">
                <div class="timer<?=$bet['winner_id'] === $user['user_id'] ? ' timer--win' : get_lots_timer_class($bet['lot_expiry_date']); ?>"><?=$bet['winner_id'] === $user['user_id'] ? 'Ставка выиграла' : get_lot_expiry_time($bet['lot_expiry_date']); ?></div>
            </td>
            <td class="rates__price">
                <?=price_format($bet['amount'], false); ?> р
            </td>
            <td class="rates__time">
                <?=get_bet_add_time($bet['adding_date']); ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</section>
