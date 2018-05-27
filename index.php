<?php
date_default_timezone_set('Europe/Moscow');

require_once 'functions.php';
require_once 'queries.php';
require_once 'validators.php';

$is_auth = (bool)rand(0, 1);

$user_name = 'Константин';
$user_avatar = 'img/user.jpg';

// Страница с существующим лотом
if (isset($_GET['lot'])) {
    // Проверка на число
    if (!preg_match('/^\d+$/', $_GET['lot'])) {
        return http_response_code(404);
    }
    $lot = getLot((int)$_GET['lot']);
    $bets = getBets((int)$_GET['lot']);
    // Проверка существования лота в базе
    if (!empty($lot)) {
        $title = $lot['name'];
        $page_content = renderTemplate('templates/lot.php', ['lot' => $lot, 'bets' => $bets]);
    } else {
        return http_response_code(404);
    }
}

// Страница с новым лотом
if (isset($_GET['add-lot'])) {
    $lot = [];
    $errors = [];
    if ($_POST !== []) {
        $lot = $_POST;
    }
    if (isset($_FILES['avatar'])) {
        $lot['avatar'] = $_FILES['avatar'];
    }
    if ($lot !== []) {
        $errors = validateNewLot($lot);
    }
    $page_content = renderTemplate('templates/add.php', [
        'lot' => $lot,
        'categories' => getAllCategories(),
        'errors' => $errors,
    ]);
}

// Содержимое страницы
if (!isset($page_content)) {
    $page_content = renderTemplate('templates/index.php', ['lots' => getOpenLots()]);
}

// Шаблон страниц с хедером и футером (навигацией и пр.)
$layout_content = renderTemplate('templates/layout.php', [
    'is_auth' => $is_auth,
    'title' => $title??'YetiCave',
    'user_avatar' => $user_avatar,
    'user_name' => $user_name,
    'content' => $page_content,
    'categories' => getAllCategories(),
]);
print($layout_content);