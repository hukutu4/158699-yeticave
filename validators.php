<?php

/** Валидация нового лота
 * @param array $lot
 * @return array
 */
function validateNewLot(array &$lot) {
    $errors = [];

    // Валидация категории
    if (!preg_match('/^\d+$/', $lot['category']) || empty(getCategory($lot['category']))) {
        $errors['category'] = 'Выберите категорию';
    } else {
        $lot['category'] = (int)$lot['category'];
    }

    // Валидация наименования
    if (!empty($lot['lot-name'])) {
        $lot['lot-name'] = filter_var($lot['lot-name'], FILTER_SANITIZE_STRING);
        // Обрезаем строку, если длина больше допустимой
        if (mb_strlen($lot['lot-name']) > 255) {
            $lot['lot-name'] = mb_substr($lot['lot-name'], 0, 255);
        }
    } else {
        $errors['lot-name'] = 'Введите наименование лота';
    }

    // Валидация описания
    if (!empty($lot['message'])) {
        $lot['message'] = filter_var($lot['message'], FILTER_SANITIZE_STRING);
        // Обрезаем строку, если длина больше допустимой
        if (mb_strlen($lot['message']) > 2047) {
            $lot['message'] = mb_substr($lot['message'], 0, 2047);
        }
    } else {
        $errors['message'] = 'Напишите описание лота';
    }

    // Валидация начальной цены
    if (!empty($lot['lot-rate'])) {
        $lot['lot-rate'] = filter_var($lot['lot-rate'], FILTER_SANITIZE_NUMBER_FLOAT);
        $lot['lot-rate'] = (double)$lot['lot-rate'];
    } else {
        $errors['lot-rate'] = 'Введите начальную цену';
    }

    // Валидация шага ставки
    if (!empty($lot['lot-step'])) {
        $lot['lot-step'] = filter_var($lot['lot-step'], FILTER_SANITIZE_NUMBER_FLOAT);
        $lot['lot-step'] = (double)$lot['lot-step'];
    } else {
        $errors['lot-step'] = 'Введите шаг ставки';
    }

    // Валидация даты завершения торгов
    if (!empty($lot['lot-date']) && ($date_end = date_create($lot['lot-date'])) instanceof DateTime) {
        if ($date_end->getTimestamp() < time()) {
            $errors['lot-date'] = 'Дата завершения торгов должна быть позже текущего дня';
        }
    } else {
        $errors['lot-date'] = 'Введите дату завершения торгов';
    }

    // Валидация файла аватара
    validateAvatarImage($lot, $errors);

    return $errors;
}

/** Валидация данных о новом пользователе
 * @param $new_user
 * @return array
 */
function validateNewUser(&$new_user) {
    $errors = [];

    // Валидация емейла
    if (filter_var($new_user['email'], FILTER_VALIDATE_EMAIL)) {
        $user = getUserByEmail($new_user['email']);
        if (!empty($user)) {
            $errors['email'] = 'Пользователь с указанным e-mail уже существует';
        }
        if (mb_strlen($new_user['email']) > 255) {
            $errors['email'] = 'E-mail не должен превышать 255 символов';
        }
    } else {
        $errors['email'] = 'Введите корректный e-mail';
    }

    // Хеширование пароля
    if (!empty($new_user['password'])) {
        $new_user['password'] = password_hash($new_user['password'], PASSWORD_DEFAULT);
    } else {
        $errors['password'] = 'Введите пароль';
    }

    // Фильтрация имени пользователя
    if (!empty($new_user['name'])) {
        $new_user['name'] = filter_var($new_user['name'], FILTER_SANITIZE_STRING);
        if (mb_strlen($new_user['name']) > 255) {
            $errors['name'] = 'Имя не должно превышать 255 символов';
        }
    } else {
        $errors['name'] = 'Укажите имя';
    }

    // Фильтрация контактных данных
    if (!empty($new_user['message'])) {
        $new_user['message'] = filter_var($new_user['message'], FILTER_SANITIZE_STRING);
        if (mb_strlen($new_user['message']) > 2047) {
            $errors['message'] = 'Кол-во знаков не должно превышать 2047 символов';
        }
    } else {
        $errors['message'] = 'Укажите контактную информацию для связи с Вами';
    }

    // Валидация файла аватара
    validateAvatarImage($new_user, $errors, false);

    return $errors;
}

/** Валидация файла аватара
 * @param $subject
 * @param $errors
 * @param bool $required
 * @return bool
 */
function validateAvatarImage(&$subject, &$errors, $required = true) {
    if (isset($subject['avatar']) && !empty($subject['avatar']['tmp_name'])) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_name = $subject['avatar']['tmp_name'];
        $file_size = $subject['avatar']['size'];
        $file_type = finfo_file($finfo, $file_name);
        if ($file_type !== 'image/jpeg') {
            $errors['avatar'] = "Загрузите картинку в формате jpeg";
        }
        if ($file_size > 5000000) {
            $errors['avatar'] = "Максимальный размер файла: 5Мб";
        }
        if (!isset($errors['avatar'])) {
            $file_name = $subject['avatar']['name'];
            $file_path = __DIR__ . '/img/';
            $file_url = '/img/' . $file_name;
            move_uploaded_file($subject['avatar']['tmp_name'], $file_path . $file_name);
            $subject['image-url'] = $file_url;
        }
    } elseif (empty($subject['image-url']) && $required) {
        $errors['avatar'] = 'Добавьте фотографию';
    }
    return empty($errors['avatar']);
}

/** Аутентификация пользователя при входе
 * @param $login
 * @return array
 */
function authenticate(&$login) {
    $main_err_message = 'Логин и(или) пароль указаны не корректно';
    $errors = [];
    if (filter_var($login['email'], FILTER_VALIDATE_EMAIL)) {
        $user = getUserByEmail($login['email']);
        if (empty($user)) {
            $errors['main'] = $main_err_message;
        } elseif (!password_verify($login['password'], $user['password'])) {
            $errors['main'] = $main_err_message;
        }
    } else {
        $errors['email'] = 'Введите корректный e-mail';
    }
    if (empty($login['password'])) {
        $errors['password'] = 'Укажите пароль';
    }

    return $errors;
}