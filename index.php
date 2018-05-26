<?php
date_default_timezone_set('Europe/Moscow');

require_once 'functions.php';
require_once 'queries.php';

$is_auth = (bool)rand(0, 1);

$user_name = 'Константин';
$user_avatar = 'img/user.jpg';

// Страница с лотом
if (isset($_GET['lot'])) {
    if (!preg_match('/^\d+$/', $_GET['lot'])) {
        return http_response_code(404);
    }
    $lot = getLot((int)$_GET['lot']);
    $bets = getBets((int)$_GET['lot']);
    if (!empty($lot)) {
        $title = $lot['name'];
        $page_content = renderTemplate('templates/lot.php', ['lot' => $lot, 'bets' => $bets]);
    } else {
        return http_response_code(404);
    }
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