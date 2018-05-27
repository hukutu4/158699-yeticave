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
    } else {
        $errors['lot-rate'] = 'Введите начальную цену';
    }

    // Валидация шага ставки
    if (!empty($lot['lot-step'])) {
        $lot['lot-step'] = filter_var($lot['lot-step'], FILTER_SANITIZE_NUMBER_FLOAT);
    } else {
        $errors['lot-step'] = 'Введите шаг ставки';
    }

    // Валидация даты завершения торгов
    if (!empty($lot['lot-date']) && ($date_end = date_create($lot['lot-date']))) {
        if ($date_end->getTimestamp() < time()) {
            $errors['lot-date'] = 'Дата завершения торгов должна быть позже текущего дня';
        }
    } else {
        $errors['lot-date'] = 'Введите дату завершения торгов';
    }

    return $errors;
}
