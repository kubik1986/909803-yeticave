<ul class="pagination-list">
    <?php foreach ($pagination_data as $item): ?>
        <li class="pagination-item<?=$item['class']; ?>">
            <a<?=$item['href']; ?>><?=$item['page_number']; ?></a>
        </li>
    <?php endforeach; ?>
</ul>
