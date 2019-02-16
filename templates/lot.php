<section class="lot-item container">
    <h2><?=htmlspecialchars($lot['title']); ?></h2>
    <div class="lot-item__content">
        <div class="lot-item__left">
            <div class="lot-item__image">
                <img src="<?='/' . $lot_img_path . $lot['img']; ?>" width="730" height="548" alt="">
            </div>
            <p class="lot-item__category">Категория: <span><?=$lot['category']; ?></span></p>
            <p class="lot-item__description"><?=htmlspecialchars($lot['description']); ?></p>
        </div>
        <div class="lot-item__right">
            <div class="lot-item__state">
                <?php if (is_lot_finishing($lot['expiry_date'])): ?>
                <div class="lot-item__timer timer timer--finishing">
                <?php else: ?>
                <div class="lot-item__timer timer">
                <?php endif; ?>
                    <?=get_lot_expiry_time($lot['expiry_date']); ?>
                </div>
                <div class="lot-item__cost-state">
                    <div class="lot-item__rate">
                        <span class="lot-item__amount">Текущая цена</span>
                        <span class="lot-item__cost"><?=price_format($lot['price'], false); ?></span>
                    </div>
                    <div class="lot-item__min-cost">
                        Мин. ставка <span><?=price_format(intval($lot['price']) + intval($lot['bet_step']), false); ?> р</span>
                    </div>
                </div>
                <?php if (!is_lot_closed($lot['expiry_date']) &&
                    $user &&
                    intval($user['user_id']) !== intval($lot['author_id']) &&
                    (!$bets || intval($bets[0]['user_id']) !== intval($user['user_id']))): ?>
                <form class="lot-item__form" action="https://echo.htmlacademy.ru" method="post">
                    <p class="lot-item__form-item form__item form__item--invalid">
                        <label for="cost">Ваша ставка</label>
                        <input id="cost" type="text" name="cost" placeholder="<?=price_format(intval($lot['price']) + intval($lot['bet_step']), false); ?>">
                        <span class="form__error">Текст ошибки</span>
                    </p>
                    <button type="submit" class="button">Сделать ставку</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="history">
                <h3>История ставок (<span><?=count($bets); ?></span>)</h3>
                <?php if ($bets): ?>
                <table class="history__list">
                    <?php foreach($bets as $bet): ?>
                    <tr class="history__item">
                        <td class="history__name"><?=htmlspecialchars($bet['user']); ?></td>
                        <td class="history__price"><?=price_format(intval($bet['amount']), false); ?> р</td>
                        <td class="history__time"><?=get_bet_add_time($bet['adding_date']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
