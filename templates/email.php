<h1>Поздравляем с победой</h1>
<p>Здравствуйте, <?=htmlspecialchars($winner['name']); ?>.</p>
<p>Ваша ставка для лота <a href="http://<?=$_SERVER['SERVER_NAME']; ?>/lot.php?id=<?=$lot['lot_id']; ?>"><?=htmlspecialchars($lot['title']); ?></a> победила.</p>
<p>Перейдите по ссылке <a href="http://<?=$_SERVER['SERVER_NAME']; ?>/my-lots.php">мои ставки</a>, чтобы связаться с автором объявления.</p>

<small>Интернет Аукцион "YetiCave".</small>
