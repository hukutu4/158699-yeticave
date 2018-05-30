<?php
date_default_timezone_set('Europe/Moscow');
session_start();

require_once 'functions.php';
require_once 'queries.php';
require_once 'validators.php';

$categories = getAllCategories();

// Разлогиниваем пользователя
if (isset($_GET['logout'])) {
    logout();
    header("Location: /");
    exit;
}

// Страница регистрации
if (isset($_GET['login'])) {
    $title = 'Вход';
    $login = [];
    $errors = [];
    if ($_POST !== []) {
        $login = $_POST;
    }
    if ($login !== []) {
        $errors = authenticate($login);
    }
    if ($errors === [] && $login !== []) {
        authorize($login);
        header("Location: /");
        exit;
    } else {
        $page_content = renderTemplate('templates/login.php', [
            'login' => $login,
            'errors' => $errors,
        ]);
    }
}

// Страница регистрации
if (isset($_GET['sign-up'])) {
    $title = 'Регистрация';
    $new_user = [];
    $errors = [];
    if ($_POST !== []) {
        $new_user = $_POST;
    }
    if (isset($_FILES['avatar'])) {
        $new_user['avatar'] = $_FILES['avatar'];
    }
    if ($new_user !== []) {
        $errors = validateNewUser($new_user);
    }
    if ($errors === [] && $new_user !== []) {
        addNewUser($new_user);
        header("Location: /?login");
        exit;
    } else {
        $page_content = renderTemplate('templates/sign-up.php', [
            'new_user' => $new_user,
            'errors' => $errors,
        ]);
    }
}

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
    if ($errors === [] && $lot !== []) {
        $lot_id = addLot($lot);
        header("Location: /?lot={$lot_id}");
        exit;
    } else {
        $page_content = renderTemplate('templates/add.php', [
            'lot' => $lot,
            'categories' => $categories,
            'errors' => $errors,
        ]);
    }
}

// Содержимое страницы
if (!isset($page_content)) {
    $page_content = renderTemplate('templates/index.php', ['lots' => getOpenLots(), 'categories' => $categories]);
}

// Шаблон страниц с хедером и футером (навигацией и пр.)
$layout_content = renderTemplate('templates/layout.php', [
    'title' => $title ?? 'YetiCave',
    'content' => $page_content,
    'categories' => $categories,
]);
print($layout_content);