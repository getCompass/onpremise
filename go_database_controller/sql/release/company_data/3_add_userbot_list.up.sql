use company_data;

CREATE TABLE IF NOT EXISTS `userbot_list` (
  `userbot_id` CHAR(32) NOT NULL DEFAULT '' COMMENT 'идентификатор бота',
  `status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус бота',
  `user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, за которым закреплён бот',
  `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
  `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
  `extra` JSON NOT NULL COMMENT 'доп. данные',
PRIMARY KEY (`userbot_id`),
INDEX `get_by_user_id` (`user_id` ASC) COMMENT 'Индекс для выборки по id пользователя')
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT 'используется для хранения ботов и связи сущностей бота и пользователя';

CREATE TABLE IF NOT EXISTS `userbot_conversation_rel` (
  `row_id` int NOT NULL AUTO_INCREMENT COMMENT 'идентификатор строки',
  `userbot_id` char(32) NOT NULL DEFAULT '' COMMENT 'идентификатор пользовательского бота',
  `conversation_type` tinyint NOT NULL DEFAULT '0' COMMENT 'тип диалога',
  `created_at` int NOT NULL DEFAULT '0' COMMENT 'временная метка создания записи',
  `conversation_map` varchar(255) NOT NULL DEFAULT '' COMMENT 'ключ диалога пользователя',
PRIMARY KEY (`row_id`),
INDEX `get_by_userbot_id_and_conversation` (`userbot_id` ASC, `conversation_map` ASC))
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb3
COMMENT='таблица для хранения списка ключей личных диалогов пользовательского бота';

CREATE TABLE IF NOT EXISTS `userbot_conversation_history` (
  `row_id` int NOT NULL AUTO_INCREMENT COMMENT 'идентификатор строки',
  `userbot_id` char(32) NOT NULL DEFAULT '' COMMENT 'идентификатор пользовательского бота',
  `action_type` tinyint NOT NULL DEFAULT '0' COMMENT 'тип действия',
  `created_at` int NOT NULL DEFAULT '0' COMMENT 'временная метка создания записи',
  `updated_at` int NOT NULL DEFAULT '0' COMMENT 'временная метка обновления записи',
  `conversation_map` varchar(255) NOT NULL DEFAULT '' COMMENT 'ключ диалога пользователя',
PRIMARY KEY (`row_id`),
INDEX `get_by_userbot_id_and_conversation` (`userbot_id` ASC, `conversation_map` ASC))
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb3
COMMENT='таблица для хранения списка действий над диалогами пользовательского бота';