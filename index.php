<?php
date_default_timezone_set('Europe/Moscow');
session_start();

require_once 'functions.php';

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
        $errors = validateLogin($login);
    }
    if ($errors === [] && $login !== []) {
        authorize($login);
        header("Location: /");
        exit;
    } else {
        $page_content = renderTemplate('templates/login.php', [
            'login' => $login,
            'errors' => $errors,
            'categories' => $categories,
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
            'categories' => $categories,
        ]);
    }
}

// Страница с существующим лотом
if (isset($_GET['lot'])) {
    $new_bet = [];
    $errors = [];
    if ($_POST !== [] && isset($_SESSION['user'])) {
        $new_bet = $_POST;
    }
    if ($new_bet !== []) {
        $errors = validateNewBet($new_bet);
    }
    if ($errors === [] && $new_bet !== []) {
        $lot_id = addBet($new_bet);
    }
    // Проверка на число
    if (!preg_match('/^\d+$/', $_GET['lot'])) {
        return http_response_code(404);
    }
    $lot = getLot((int)$_GET['lot']);
    $bets = getBets((int)$_GET['lot']);
    // Проверка существования лота в базе
    if (!empty($lot)) {
        $title = $lot['name'];
        $page_content = renderTemplate('templates/lot.php', [
            'lot' => $lot,
            'bets' => $bets,
            'categories' => $categories,
            'new_bet' => $new_bet,
            'errors' => $errors,
        ]);
    } else {
        return http_response_code(404);
    }
}

// Страница с новым лотом
if (isset($_GET['add-lot'])) {
    if (!isset($_SESSION['user'])) {
        return http_response_code(403);
    }
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