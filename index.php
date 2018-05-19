<?php
date_default_timezone_set('Europe/Moscow');

require_once 'functions.php';

$is_auth = (bool)rand(0, 1);

$user_name = 'Константин';
$user_avatar = 'img/user.jpg';

// Подключение к БД
$db = mysqli_connect('localhost', 'yeti', 'yeti', 'yeti');
if ($db === false) {
    print("Ошибка подключения: " . mysqli_connect_error());
    die();
}

// Получаем категории
$sql = "SELECT
  *
FROM
  categories
;";
$res = $db->query($sql);
$categories = [];
if ($res !== false) {
    $categories = $res->fetch_all(MYSQLI_ASSOC);
}

// Получаем открытые лоты
$sql = "SELECT
  l.name,
  l.starting_price as price,
  l.image_url as url,
  IF(MAX(b.price) IS NOT NULL, MAX(b.price), l.starting_price) AS current_price,
  COUNT(distinct b.id) AS bets_count,
  c.name AS category
FROM
  lots l
LEFT JOIN
  bets b ON l.id = b.lot_id
INNER JOIN
  categories c ON l.category_id = c.id
WHERE
  l.winner_id IS NULL
GROUP BY
  l.name,
  l.starting_price,
  l.image_url,
  c.name,
  l.created_at
ORDER BY
  l.created_at DESC
;";
$res = $db->query($sql);
$ads = [];
if ($res !== false) {
    $ads = $res->fetch_all(MYSQLI_ASSOC);
}

$page_content = renderTemplate('templates/index.php', compact('ads'));
$layout_content = renderTemplate('templates/layout.php', [
    'is_auth' => $is_auth,
    'title' => 'Главная',
    'user_avatar' => $user_avatar,
    'user_name' => $user_name,
    'content' => $page_content,
    'categories' => $categories,
]);
print($layout_content);