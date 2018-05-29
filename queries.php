<?php

/** Получить коннект к базе
 * @return mysqli
 */
function getDbConnection() {
    static $db;
    // Проверяем, есть ли уже коннект к БД
    if (is_null($db)) {
        // Если коннекта нет - подключаемся к БД
        $db = mysqli_connect('localhost', 'yeti', 'yeti', 'yeti');
        if ($db === false) {
            print("Ошибка подключения: " . mysqli_connect_error());
            die();
        }
    }
    return $db;
}

/** Получаем категории
 * @return array|mixed
 */
function getAllCategories() {
    $db = getDbConnection();
    $sql = "SELECT
      *
    FROM
      categories";
    $mysqli_result = $db->query($sql);
    $result = [];
    if ($mysqli_result !== false) {
        $result = $mysqli_result->fetch_all(MYSQLI_ASSOC);
    }
    return $result;
}

/** Получаем категорию по id
 * @param int $id
 * @return array|mixed
 */
function getCategory($id) {
    $db = getDbConnection();
    $sql = "SELECT
      *
    FROM
      categories
    WHERE 
      id = ?";
    $mysqli_stmt = $db->prepare($sql);
    $mysqli_stmt->bind_param('i', $id);
    $mysqli_stmt->execute();
    $mysqli_result = $mysqli_stmt->get_result();
    $result = [];
    if ($mysqli_result !== false) {
        $result = $mysqli_result->fetch_all(MYSQLI_ASSOC);
    }
    return $result[0]??[];
}

/** Получаем открытые лоты
 * @return array|mixed
 */
function getOpenLots() {
    $db = getDbConnection();
    $sql = "SELECT
      l.id,
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
      l.id,
      l.name,
      l.starting_price,
      l.image_url,
      c.name,
      l.created_at
    ORDER BY
      l.created_at DESC";
    $mysqli_result = $db->query($sql);
    $result = [];
    if ($mysqli_result !== false) {
        $result = $mysqli_result->fetch_all(MYSQLI_ASSOC);
    }
    return $result;
}

/** Показать лот (и его категорию) по id
 * @param int $id
 * @return array|mixed
 */
function getLot(int $id) {
    $db = getDbConnection();
    $sql = "SELECT
      l.*,
      c.name as category_name,
      IFNULL((select MAX(b.price) from bets b where b.lot_id = l.id), l.starting_price) AS current_price
    FROM
      lots l
    INNER JOIN
      categories c ON l.category_id = c.id
    WHERE
      l.id = ?";
    $mysqli_stmt = $db->prepare($sql);
    $mysqli_stmt->bind_param('i', $id);
    $mysqli_stmt->execute();
    $mysqli_result = $mysqli_stmt->get_result();
    $result = [];
    if ($mysqli_result !== false) {
        $result = $mysqli_result->fetch_all(MYSQLI_ASSOC);
    }
    return $result[0]??[];
}

function getBets(int $lot_id) {
    $db = getDbConnection();
    $sql = "SELECT
      u.name as 'user_name',
      b.price,
      b.created_at
    FROM
      bets b
    inner join
      users u on u.id = b.user_id
    WHERE
      lot_id = ?
    ORDER BY
      created_at DESC
    LIMIT 10";
    $mysqli_stmt = $db->prepare($sql);
    $mysqli_stmt->bind_param('i', $lot_id);
    $mysqli_stmt->execute();
    $mysqli_result = $mysqli_stmt->get_result();
    $result = [];
    if ($mysqli_result !== false) {
        $result = $mysqli_result->fetch_all(MYSQLI_ASSOC);
    }
    return $result;
}

/** Добавление нового лота
 * @param array $lot
 * @return bool
 */
function addLot(array $lot) {
    $db = getDbConnection();
    $sql = "INSERT INTO `lots`(`author_id`, `category_id`, `name`, `description`, `image_url`, `starting_price`, `bet_step`, `created_at`, `updated_at`) VALUES (?,?,?,?,?,?,?,?,?)";
    $mysqli_stmt = $db->prepare($sql);
    // TODO: убрать хардкод после реализации аутентификации пользователей
    $author_id = 1;
    $lot_created_time = time();
    $lot_end_time = strtotime($lot['lot-date']);
    $mysqli_stmt->bind_param('iisssddii',
        $author_id,
        $lot['category'],
        $lot['lot-name'],
        $lot['message'],
        $lot['image-url'],
        $lot['lot-rate'],
        $lot['lot-step'],
        $lot_created_time,
        $lot_end_time
    );
    if ($mysqli_stmt->execute()) {
        $result = $db->insert_id;
    } else {
        $result = false;
    }
    return $result;
}

/** Получить пользователя по email
 * @param string $email
 * @return array|mixed
 */
function getUserByEmail(string $email) {
    $db = getDbConnection();
    $sql = "SELECT
      *
    FROM
      users
    WHERE
      email = ?";
    $mysqli_stmt = $db->prepare($sql);
    $mysqli_stmt->bind_param('s', $email);
    $mysqli_stmt->execute();
    $mysqli_result = $mysqli_stmt->get_result();
    $result = [];
    if ($mysqli_result !== false) {
        $result = $mysqli_result->fetch_all(MYSQLI_ASSOC);
    }
    return $result[0]??[];
}

/** Добавление нового пользователя
 * @param array $user
 * @return bool
 */
function addNewUser(array $user) {
    $db = getDbConnection();
    $sql = "INSERT INTO `users`(`email`, `name`, `password`, `avatar_url`, `contacts`, `created_at`) VALUES (?,?,?,?,?,?)";
    $mysqli_stmt = $db->prepare($sql);
    $created_at = time();
    $mysqli_stmt->bind_param('sssssi',
        $user['email'],
        $user['name'],
        $user['password'],
        $user['image-url'],
        $user['message'],
        $created_at
    );
    if ($mysqli_stmt->execute()) {
        $result = $db->insert_id;
    } else {
        $result = false;
    }
    return $result;
}