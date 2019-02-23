<?php if ($is_main_page): ?>
<section class="promo">
    <h2 class="promo__title">Нужен стафф для катки?</h2>
    <p class="promo__text">На нашем интернет-аукционе ты найдёшь самое эксклюзивное сноубордическое и горнолыжное снаряжение.</p>
    <ul class="promo__list">
        <?php foreach ($categories as $category): ?>
        <li class="promo__item promo__item--<?=$category['class']; ?>">
            <a class="promo__link" href="all-lots.php?category=<?=$category['category_id']; ?>"><?=$category['name']; ?></a>
        </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>
<?php if (!$is_main_page): ?>
<div class="container">
<?php endif; ?>
    <section class="lots">
        <div class="lots__header">
            <h2>Открытые лоты</h2>
        </div>
        <?=$lots_list; ?>
    </section>
<?php if (!empty($pagination_data)): ?>
<?=include_template('_pagination.php', ['pagination_data' => $pagination_data]); ?>
<?php endif; ?>
<?php if (!$is_main_page): ?>
</div>
<?php endif; ?>
