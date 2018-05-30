<?php

/** Шаблонизатор
 * @param string $filename
 * @param array $params
 * @return string
 */
function renderTemplate($filename, $params = []) {
    if (file_exists($filename)) {
        extract($params);
        ob_start();
        require $filename;
        return ob_get_clean();
    }
    return '';
}

/** Форматирует целое число, представляя его в виде строки, с разделителем пробелом для тысяч, дополненным знаком рубля.
 * @param int $price
 * @return string
 */
function rurNumberFormat(int $price) {
    return number_format($price, 0, '.', ' ') . '<b class="rub">р</b>';
}

/** Авторизация пользователя
 * @param array $login
 * @return bool
 */
function authorize($login) {
    $user = getUserByEmail($login['email']);
    if (!empty($user)) {
        unset($user['password']);
        $_SESSION['user'] = $user;
        return true;
    }
    return false;
}

/**
 * Разлогиниваем пользователя
 */
function logout():void {
    unset($_SESSION['user']);
}