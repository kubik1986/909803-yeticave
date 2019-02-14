<section class="lots">
    <div class="lots__header">
        <h2>Все лоты в категории «<?=$categories[$category_id - 1]['name']; ?>»</h2>
    </div>
    <?=$lots_list; ?>
</section>
<?php if ($pagination_data): ?>
<?=include_template('_pagination.php', ['pagination_data' => $pagination_data]); ?>
<?php endif; ?>
