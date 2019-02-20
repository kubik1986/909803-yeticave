<section class="rates container">
    <h2>Мои ставки</h2>
    <?php if (empty($bets)): ?>
    <p>Вы еще не делали ни одной ставки.</p>
    <?php else: ?>
    <table class="rates__list">
        <?php foreach ($bets as $bet): ?>
        <?php
            $rate_add_class = '';
            if (is_lot_closed($bet['lot_expiry_date'])) {
                $rate_add_class = ' rates__item--end';
            }
            if ($bet['winner_id'] === $user['user_id']) {
                $rate_add_class = ' rates__item--win';
            }
        ?>
        <tr class="rates__item<?=$rate_add_class; ?>">
            <td class="rates__info">
                <div class="rates__img">
                  <img src="<?=$lot_img_path . $bet['img']; ?>" width="54" height="40" alt="Изображение лота">
                </div>
                <?php if ($bet['winner_id'] === $user['user_id']): ?>
                <div>
                    <h3 class="rates__title"><a href="lot.php/?id=<?=$bet['lot_id']; ?>"><?=htmlspecialchars($bet['lot_title']); ?></a></h3>
                    <p><?=htmlspecialchars($bet['lot_author_contacts']); ?></p>
                </div>
                <?php else: ?>
                <h3 class="rates__title"><a href="lot.php/?id=<?=$bet['lot_id']; ?>"><?=htmlspecialchars($bet['lot_title']); ?></a></h3>
                <?php endif; ?>
            </td>
            <td class="rates__category">
                <?=$bet['category']; ?>
            </td>
            <?php
                $timer_add_class = '';
                if (is_lot_finishing($bet['lot_expiry_date'])) {
                    $timer_add_class = ' timer--finishing';
                }
                if (is_lot_closed($bet['lot_expiry_date'])) {
                    $timer_add_class = ' timer--end';
                }
                if ($bet['winner_id'] === $user['user_id']) {
                    $timer_add_class = ' timer--win';
                }
            ?>
            <td class="rates__timer">
                <div class="timer<?=$timer_add_class; ?>"><?=$bet['winner_id'] === $user['user_id'] ? 'Ставка выиграла' : get_lot_expiry_time($bet['lot_expiry_date']); ?></div>
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
