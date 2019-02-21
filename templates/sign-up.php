<form class="form container<?=empty($errors) ? '' : ' form--invalid'; ?>" action="sign-up.php" method="post" enctype="multipart/form-data">
    <h2>Регистрация нового аккаунта</h2>
    <div class="form__item<?=!isset($errors['email']) ? '' : ' form__item--invalid'; ?>">
        <label for="email">E-mail*</label>
        <input id="email" type="text" name="email" placeholder="Введите e-mail" required<?=empty($data['email']) ? '' : ' value="' . $data['email'] . '"'; ?>>
        <span class="form__error"><?=!isset($errors['email']) ? '' : $errors['email']; ?></span>
    </div>
    <div class="form__item<?=!isset($errors['password']) ? '' : ' form__item--invalid'; ?>">
        <label for="password">Пароль*</label>
        <input id="password" type="text" name="password" placeholder="Введите пароль" required<?=empty($data['password']) ? '' : ' value="' . $data['password'] . '"'; ?>>
        <span class="form__error"><?=!isset($errors['password']) ? '' : $errors['password']; ?></span>
    </div>
    <div class="form__item<?=!isset($errors['name']) ? '' : ' form__item--invalid'; ?>">
        <label for="name">Имя*</label>
        <input id="name" type="text" name="name" placeholder="Введите имя" required<?=empty($data['name']) ? '' : ' value="' . $data['name'] . '"'; ?>>
        <span class="form__error"><?=!isset($errors['name']) ? '' : $errors['name']; ?></span>
    </div>
    <div class="form__item<?=!isset($errors['message']) ? '' : ' form__item--invalid'; ?>">
        <label for="message">Контактные данные*</label>
        <textarea id="message" name="message" placeholder="Напишите как с вами связаться" required><?=empty($data['message']) ? '' : $data['message']; ?></textarea>
        <span class="form__error"><?=!isset($errors['message']) ? '' : $errors['message']; ?></span>
    </div>
    <div class="form__item form__item--file form__item--last<?=!isset($errors['avatar']) ? '' : ' form__item--invalid'; ?>">
        <label>Аватар</label>
        <div class="preview">
            <button class="preview__remove" type="button">x</button>
            <div class="preview__img">
                <img src="img/avatar.jpg" width="113" height="113" alt="Изображение лота">
            </div>
        </div>
        <div class="form__input-file">
            <input class="visually-hidden" id="photo2" type="file" name="avatar" value="">
            <label for="photo2">
                <span>+ Добавить</span>
            </label>
        </div>
        <span class="form__error"><?=!isset($errors['avatar']) ? '' : $errors['avatar']; ?></span>
    </div>
    <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
    <button type="submit" class="button">Зарегистрироваться</button>
    <a class="text-link" href="login.php">Уже есть аккаунт</a>
</form>
