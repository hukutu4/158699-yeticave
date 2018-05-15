DROP DATABASE IF EXISTS yeti;
CREATE DATABASE yeti;

USE yeti;

CREATE TABLE `users` (
  `id`                int(11) NOT NULL AUTO_INCREMENT,
  `date_registration` datetime         DEFAULT NULL,
  `email`             varchar(255)     DEFAULT NULL,
  `name`              varchar(255)     DEFAULT NULL,
  `password`          varchar(255)     DEFAULT NULL,
  `avatar_url`        varchar(255)     DEFAULT NULL,
  `contacts`          text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_uindex` (`email`),
  UNIQUE KEY `users_name_uindex` (`name`),
  KEY `users_date_registration_index` (`date_registration`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `categories` (
  `id`   int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255)     DEFAULT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 7
  DEFAULT CHARSET = utf8;

CREATE TABLE `lots` (
  `id`             int(11) NOT NULL        AUTO_INCREMENT,
  `date_create`    datetime                DEFAULT NULL,
  `name`           varchar(255)            DEFAULT NULL,
  `description`    text,
  `image_url`      varchar(255)            DEFAULT NULL,
  `starting_price` decimal(19, 2) unsigned DEFAULT NULL,
  `date_end`       datetime                DEFAULT NULL,
  `bet_step`       decimal(19, 2) unsigned DEFAULT NULL,
  `author`         int(11)                 DEFAULT NULL,
  `winner`         int(11)                 DEFAULT NULL,
  `category_id`    int(11)                 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lots_date_create_index` (`date_create`),
  KEY `lots_starting_price_index` (`starting_price`),
  KEY `lots_date_end_index` (`date_end`),
  KEY `lots_author_index` (`author`),
  KEY `lots_winner_index` (`winner`),
  KEY `lots_category_id_index` (`category_id`),
  CONSTRAINT `lots_categories_id_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `lots_users_id_fk` FOREIGN KEY (`author`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `lots_users_id_fk_2` FOREIGN KEY (`winner`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `bets` (
  `id`          int(11) NOT NULL        AUTO_INCREMENT,
  `date_create` datetime                DEFAULT NULL,
  `price`       decimal(19, 2) unsigned DEFAULT NULL,
  `user_id`     int(11)                 DEFAULT NULL,
  `lot_id`      int(11)                 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bets_date_create_index` (`date_create`),
  KEY `bets_user_id_index` (`user_id`),
  KEY `bets_lot_id_index` (`lot_id`),
  CONSTRAINT `bets_lots_id_fk` FOREIGN KEY (`lot_id`) REFERENCES `lots` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `bets_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
