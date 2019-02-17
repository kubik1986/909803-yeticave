<?php if (empty($lots)): ?>
<p>Активные лоты не найдены.</p>
<?php else: ?>
<ul class="lots__list">
    <?php foreach ($lots as $lot): ?>
    <li class="lots__item lot">
        <div class="lot__image">
            <img src="<?='/' . $lot_img_path . $lot['img']; ?>" width="350" height="260" alt="">
        </div>
        <div class="lot__info">
            <span class="lot__category"><?=$lot['category']; ?></span>
            <h3 class="lot__title"><a class="text-link" href="/lot.php/?id=<?=$lot['lot_id']; ?>"><?=htmlspecialchars($lot['title']); ?></a></h3>
            <div class="lot__state">
                <div class="lot__rate">
                    <?php if (intval($lot['bets_count']) === 0): ?>
                    <span class="lot__amount">Стартовая цена</span>
                    <span class="lot__cost"><?=price_format($lot['starting_price']); ?></span>
                    <?php else: ?>
                    <span class="lot__amount"><?=$lot['bets_count']; ?> <?=num_format($lot['bets_count'], 'ставка'); ?></span>
                    <span class="lot__cost"><?=price_format($lot['price']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="lot__timer timer<?=is_lot_finishing($lot['expiry_date']) ? ' timer--finishing' : ''; ?>">
                    <?=get_lot_expiry_time($lot['expiry_date']); ?>
                </div>
            </div>
        </div>
    </li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>
