<form class="form form--add-lot container<?=empty($errors) ? '' : ' form--invalid'; ?>" action="add-lot.php" method="post">
    <h2>Добавление лота</h2>
    <div class="form__container-two">
        <div class="form__item<?=!isset($errors['lot-name']) ? '' : ' form__item--invalid'; ?>">
            <label for="lot-name">Наименование</label>
            <input id="lot-name" type="text" name="lot-name" placeholder="Введите наименование лота" required<?=empty($data['lot-name']) ? '' : ' value="' . $data['lot-name'] . '"'; ?>>
            <span class="form__error"><?=!isset($errors['lot-name']) ? '' : $errors['lot-name']; ?></span>
        </div>
        <div class="form__item<?=!isset($errors['category']) ? '' : ' form__item--invalid'; ?>">
            <label for="category">Категория</label>
            <select id="category" name="category" required>
                <option value="" selected disabled>Выберите категорию</option>
                <?php foreach ($categories as $category): ?>
                <option value="<?=$category['category_id']; ?>"<?=(!empty($data['category']) && $data['category'] === $category['category_id']) ? ' selected' : ''; ?>><?=$category['name']; ?></option>
                <?php endforeach; ?>
            </select>
            <span class="form__error"><?=!isset($errors['category']) ? '' : $errors['category']; ?></span>
        </div>
    </div>
    <div class="form__item form__item--wide<?=!isset($errors['message']) ? '' : ' form__item--invalid'; ?>">
        <label for="message">Описание</label>
        <textarea id="message" name="message" placeholder="Напишите описание лота" required><?=empty($data['message']) ? '' : $data['message']; ?></textarea>
        <span class="form__error"><?=!isset($errors['message']) ? '' : $errors['message']; ?></span>
    </div>
    <div class="form__item form__item--file">
        <label>Изображение</label>
        <div class="form__input-file">
            <input class="visually-hidden" id="photo2" type="file" name="photo">
            <label for="photo2">
                <span>+ Добавить</span>
            </label>
        </div>
        <span class="form__error"><?=!isset($errors['photo']) ? '' : $errors['photo']; ?></span>
    </div>
    <div class="form__container-three">
        <div class="form__item form__item--small<?=!isset($errors['lot-rate']) ? '' : ' form__item--invalid'; ?>">
            <label for="lot-rate">Начальная цена</label>
            <input id="lot-rate" type="number" name="lot-rate" placeholder="0" required<?=empty($data['lot-rate']) ? '' : ' value="' . $data['lot-rate'] . '"'; ?>>
            <span class="form__error"><?=!isset($errors['lot-rate']) ? '' : $errors['lot-rate']; ?></span>
        </div>
        <div class="form__item form__item--small<?=!isset($errors['lot-step']) ? '' : ' form__item--invalid'; ?>">
            <label for="lot-step">Шаг ставки</label>
            <input id="lot-step" type="number" name="lot-step" placeholder="0" required<?=empty($data['lot-step']) ? '' : ' value="' . $data['lot-step'] . '"'; ?>>
            <span class="form__error"><?=!isset($errors['lot-step']) ? '' : $errors['lot-step']; ?></span>
        </div>
        <div class="form__item<?=!isset($errors['lot-date']) ? '' : ' form__item--invalid'; ?>">
            <label for="lot-date">Дата окончания торгов</label>
            <input class="form__input-date" id="lot-date" type="date" name="lot-date" required<?=empty($data['lot-date']) ? '' : ' value="' . $data['lot-date'] . '"'; ?>>
            <span class="form__error"><?=!isset($errors['lot-date']) ? '' : $errors['lot-date']; ?></span>
        </div>
    </div>
    <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
    <button type="submit" class="button">Добавить лот</button>
</form>
