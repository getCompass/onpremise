USE `pivot_mail`;

CREATE TABLE IF NOT EXISTS `pivot_mail`.`mail_change_story` (
	`change_mail_story_id` BIGINT(20) AUTO_INCREMENT NOT NULL COMMENT 'id процесса ',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус процесса',
	`stage` INT(11) NOT NULL DEFAULT 0 COMMENT 'этап процесса',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество ошибок',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда время жизни процесса истечет',
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'сессия, к которой процесс привязан',
	PRIMARY KEY (`change_mail_story_id`),
	INDEX `get_unused` (`expires_at`, `status`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории смены почты пользователей';

CREATE TABLE IF NOT EXISTS `pivot_mail`.`mail_change_via_code_story` (
	`change_mail_story_id` BIGINT(20) NOT NULL COMMENT 'id процесса',
	`mail` VARCHAR(256) NOT NULL COMMENT 'текущая почта',
	`mail_new` VARCHAR(256) NOT NULL COMMENT 'новая почта',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'завершено ли подтверждение кодом',
	`stage` INT(11) NOT NULL DEFAULT 0 COMMENT 'этап смены номера',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправок проверочного кода',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во ошибок',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда можно переотправить',
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'id сообщения с проверочным кодом',
	`code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш проверочного кода',
	PRIMARY KEY (`change_mail_story_id`, `mail`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории подтверждения кодом на почту для смены почты';

CREATE TABLE IF NOT EXISTS `pivot_mail`.`mail_password_story` (
	`password_mail_story_id` BIGINT(20) AUTO_INCREMENT NOT NULL COMMENT 'id процесса ',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус процесса',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип процесса',
	`stage` INT(11) NOT NULL DEFAULT 0 COMMENT 'этап процесса',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество ошибок',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда время жизни процесса истечет',
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'сессия, к которой процесс привязан',
	PRIMARY KEY (`password_mail_story_id`),
	INDEX `get_unused` (`expires_at`, `status`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории изменения пароля связанной с почтой пользователей';

CREATE TABLE IF NOT EXISTS `pivot_mail`.`mail_password_via_code_story` (
	`password_mail_story_id` BIGINT(20) NOT NULL COMMENT 'id процесса',
	`mail` VARCHAR(256) NOT NULL COMMENT 'почта',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'завершено ли подтверждение кодом',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип процесса',
	`stage` INT(11) NOT NULL DEFAULT 0 COMMENT 'этап смены номера',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправок проверочного кода',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во ошибок',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда можно переотправить',
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'id сообщения с проверочным кодом',
	`code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш проверочного кода',
	PRIMARY KEY (`password_mail_story_id`, `mail`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории подтверждения кодом на почту для изменения пароля связанной с почтой пользователей';

CREATE TABLE IF NOT EXISTS `pivot_mail`.`mail_add_story` (
	`add_mail_story_id` BIGINT(20) AUTO_INCREMENT NOT NULL COMMENT 'id процесса ',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус процесса',
	`stage` INT(11) NOT NULL DEFAULT 0 COMMENT 'этап процесса',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество ошибок',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда время жизни процесса истечет',
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'сессия, к которой процесс привязан',
	PRIMARY KEY (`add_mail_story_id`),
	INDEX `get_unused` (`expires_at`, `status`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории добавления почты пользователей';
CREATE TABLE IF NOT EXISTS `pivot_mail`.`mail_password_confirm_story` (
    `confirm_mail_password_story_id` BIGINT NOT NULL AUTO_INCREMENT COMMENT 'id попытки действия подтверждения паролем связанных с почтой',
    `user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id пользователя',
    `status` TINYINT NOT NULL DEFAULT 0 COMMENT 'статус попытки',
    `type` INT NOT NULL DEFAULT 0 COMMENT 'тип записи',
    `stage` INT NOT NULL DEFAULT 0 COMMENT 'шаг подтверждения действия',
    `error_count` INT NOT NULL DEFAULT 0 COMMENT 'количество ошибок',
    `created_at` INT NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `expires_at` INT NOT NULL DEFAULT 0 COMMENT 'время истечения срока попытки',
    `session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'идентификатор сессии, с которой была инициирована попытка подтверждения через пароль',
    PRIMARY KEY (`confirm_mail_password_story_id`),
    INDEX `get_unused` (`status`, `expires_at`))
    ENGINE = InnoDB
    DEFAULT CHARACTER SET = utf8
    COMMENT 'Таблица со всеми попытками подтверждения действий через ввод пароля связанного с почтой пользователя';

CREATE TABLE IF NOT EXISTS `pivot_mail`.`mail_add_via_code_story` (
	`add_mail_story_id` BIGINT(20) NOT NULL COMMENT 'id процесса',
	`mail` VARCHAR(256) NOT NULL COMMENT 'почта',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'завершено ли подтверждение кодом',
	`stage` INT(11) NOT NULL DEFAULT 0 COMMENT 'этап смены номера',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправок проверочного кода',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во ошибок',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда можно переотправить',
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'id сообщения с проверочным кодом',
	`code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш проверочного кода',
	PRIMARY KEY (`add_mail_story_id`, `mail`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории подтверждения кодом на почту для добавления почты';