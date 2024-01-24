use company_data;

CREATE TABLE IF NOT EXISTS `premium_payment_request_list` (
   	`requested_by_user_id` BIGINT NOT NULL COMMENT 'идентификатор пользователя, запросившего премиум',
   	`is_payed` TINYINT NOT NULL DEFAULT 0 COMMENT 'оплачен ли премиум',
    	`requested_at` INT NOT NULL DEFAULT 0 COMMENT 'время запроса премиума',
    	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
    	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
	PRIMARY KEY (`requested_by_user_id`),
	INDEX `requested_at.is_payed.requested_by_user_id` (`requested_at` DESC, `is_payed` DESC, `requested_by_user_id` DESC) COMMENT 'Индекс для получения активных запросов'
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT 'таблица запросов премиума сотрудниками';

CREATE TABLE IF NOT EXISTS `premium_payment_request_menu` (
	`user_id` BIGINT NOT NULL COMMENT 'идентификатор пользователя меню',
	`requested_by_user_id` BIGINT NOT NULL COMMENT 'идентификатор пользователя, запросившего премиум',
	`is_unread` TINYINT NOT NULL DEFAULT 0 COMMENT 'является ли запрос непрочитанным',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
	PRIMARY KEY (`user_id`, `requested_by_user_id`),
	INDEX `user_id.is_unread` (`user_id` ASC, `is_unread` ASC) COMMENT 'Индекс для получения непрочитанных заявок пользователя'
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT 'меню пользователей с запросами на оплату премиума';

CREATE INDEX `full_name` ON `member_list` (`full_name`);
CREATE INDEX `company_joined_at` ON `member_list` (`company_joined_at`);