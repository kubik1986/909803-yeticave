<section class="lots">
    <h2>Все лоты в категории «<?=$categories[$category_id - 1]['name']; ?>»</h2>
    <?=$lots_list; ?>
</section>
<?php if (!empty($pagination_data)): ?>
<?=include_template('_pagination.php', ['pagination_data' => $pagination_data]); ?>
<?php endif; ?>
