USE `pivot_company_10m`;

CREATE TABLE IF NOT EXISTS `tariff_plan_1` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_2` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_3` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_4` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_5` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_6` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_7` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_8` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_9` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_10` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_history_1` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица истории тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_history_2` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица истории тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_history_3` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица истории тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_history_4` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица истории тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_history_5` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица истории тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_history_6` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица истории тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_history_7` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица истории тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_history_8` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица истории тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_history_9` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица истории тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_history_10` (
	`id` INT  NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип тарифного плана',
	`plan_id` INT NOT NULL DEFAULT 0 COMMENT 'идентификатор плана',
	`valid_till` INT NOT NULL DEFAULT 0 COMMENT 'срок, до которого запись считается валидной и не может быть удалена из таблицы',
	`active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия',
	`free_active_till` INT NOT NULL DEFAULT 0 COMMENT 'дата действия бесплатной активации',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`option_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список действующих опций плана',
	`payment_info` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'доп данные',
	PRIMARY KEY (`id`),
	INDEX `space_id.valid_till` (`space_id` ASC, `valid_till` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица истории тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_observe` (
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`observe_at` INT NOT NULL DEFAULT 0 COMMENT 'время запуска observe-действия',
	`report_after` INT NOT NULL DEFAULT 0 COMMENT 'время, когда нужно сообщить о проблеме в обсервере',
	`last_error_logs` MEDIUMTEXT NOT NULL COMMENT 'логи последней ошибки обсервера',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`space_id`),
        INDEX `observe_at` (`observe_at` ASC),
        INDEX `report_after` (`report_after` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица обсервера тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_task` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип задачи',
	`status` INT NOT NULL DEFAULT 0 COMMENT 'статус задачи',
	`need_work` INT NOT NULL DEFAULT 0 COMMENT 'когда нужно выполнить задачу',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'дата обновления записи',
	`logs` MEDIUMTEXT NOT NULL COMMENT 'логи выполнения задачи',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'экстра данные выполненной задачи',
	PRIMARY KEY (`id`),
	INDEX `status.need_work` (`status` ASC, `need_work` ASC),
	INDEX `space_id` (`space_id` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица задач обсервера тарифных планов';

CREATE TABLE IF NOT EXISTS `tariff_plan_task_history` (
	`id` INT NOT NULL COMMENT 'ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'id компании',
	`type` INT NOT NULL DEFAULT 0 COMMENT 'тип задачи',
	`status` INT NOT NULL DEFAULT 0 COMMENT 'статус задачи',
	`in_queue_time` INT NOT NULL DEFAULT 0 COMMENT 'сколько времени задача была в очереди',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`logs` MEDIUMTEXT NOT NULL COMMENT 'логи выполнения задачи',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'экстра данные выполненной задачи',
	PRIMARY KEY (`id`),
	INDEX `created_at` (`created_at` ASC),
	INDEX `space_id` (`space_id` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица истории задач обсервера тарифных планов';