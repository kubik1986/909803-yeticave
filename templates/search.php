<div class="container">
    <section class="lots">
        <h2>Результаты поиска по запросу «<span><?=htmlspecialchars($search['text']); ?></span>»<?=empty($search['category']) ? '' : ' в категории «' . $search['category'] . '»'; ?></h2>
        <?=$lots_list; ?>
    </section>
    <?php if (!empty($pagination_data)): ?>
    <?=include_template('_pagination.php', ['pagination_data' => $pagination_data]); ?>
    <?php endif; ?>
</div>
