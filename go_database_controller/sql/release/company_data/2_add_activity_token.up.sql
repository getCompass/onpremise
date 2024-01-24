use company_data;

CREATE TABLE IF NOT EXISTS `hibernation_delay_token_list` (
	`token_uniq` varchar(12) NOT NULL COMMENT 'токен активности',
	`user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
	`hibernation_delayed_till` INT(11) NOT NULL COMMENT 'до какого временя отложена гибернация',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата изменения записи',
	PRIMARY KEY (`token_uniq`, `user_id`),
	INDEX `hibernation_delayed_till` (`hibernation_delayed_till` ASC))
ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='таблица для хранения токенов активности пользователей компании';

CREATE INDEX `get_status_expires` ON `invite_link_list` (`status`, `expires_at` DESC);