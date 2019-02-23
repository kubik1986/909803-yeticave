<form class="form container<?=empty($errors) && !$is_auth_error ? '' : ' form--invalid'; ?>" action="login.php" method="post">
    <h2>Вход</h2>
    <div class="form__item<?=!isset($errors['email']) ? '' : ' form__item--invalid'; ?>">
        <label for="email">E-mail*</label>
        <input id="email" type="text" name="email" placeholder="Введите e-mail" required<?=empty($data['email']) ? '' : ' value="' . $data['email'] . '"'; ?>>
        <span class="form__error"><?=!isset($errors['email']) ? '' : $errors['email']; ?></span>
    </div>
    <div class="form__item form__item--last<?=!isset($errors['password']) ? '' : ' form__item--invalid'; ?>">
        <label for="password">Пароль*</label>
        <input id="password" type="password" name="password" placeholder="Введите пароль" required>
        <span class="form__error"><?=!isset($errors['password']) ? '' : $errors['password']; ?></span>
    </div>
    <?php if ($is_auth_error): ?>
    <span class="form__error form__error--bottom">Вы ввели неверный e-mail / пароль.</span>
    <?php endif; ?>
    <button type="submit" class="button">Войти</button>
</form>
