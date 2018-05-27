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
    if (isset($lot['avatar']) && !empty($lot['avatar']['tmp_name'])) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_name = $lot['avatar']['tmp_name'];
        $file_size = $lot['avatar']['size'];
        $file_type = finfo_file($finfo, $file_name);
        if ($file_type !== 'image/jpeg') {
            $errors['avatar'] = "Загрузите картинку в формате jpeg";
        }
        if ($file_size > 5000000) {
            $errors['avatar'] = "Максимальный размер файла: 5Мб";
        }
        if (!isset($errors['avatar'])) {
            $file_name = $lot['avatar']['name'];
            $file_path = __DIR__ . '/img/';
            $file_url = '/img/' . $file_name;
            move_uploaded_file($lot['avatar']['tmp_name'], $file_path . $file_name);
            $lot['image-url'] = $file_url;
        }
    } elseif (empty($lot['image-url'])) {
        $errors['avatar'] = 'Добавьте фотографию';
    }

    return $errors;
}
