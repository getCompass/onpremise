USE `auth`;

CREATE TABLE IF NOT EXISTS `auth`.`api_token_list` (
`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'Идентификатор пользователя',
`api_token` VARCHAR(32) NOT NULL COMMENT 'Api токен',
`created_at` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'время создания записи',
`updated_at` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
`expires_at` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'время истечения ключа',
`name` VARCHAR(255) NOT NULL COMMENT 'Название ключа, заданное пользователем',
`scope_list` MEDIUMTEXT NOT NULL COMMENT 'Список зон ответственности, к которым имеет доступ ключ',
`extra` MEDIUMTEXT NOT NULL COMMENT 'Дополнительная информация о ключе',
PRIMARY KEY (`user_id`,`api_token`),
INDEX `expires_at` (`expires_at`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для хранения ключей интеграции';