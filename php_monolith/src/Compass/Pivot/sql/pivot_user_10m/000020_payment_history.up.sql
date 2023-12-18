CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`space_payment_history_1` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `user_id` (user_id) COMMENT 'для выборки записей конкретного пользователя')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат совершенные пользователем';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`space_payment_history_2` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `user_id` (user_id) COMMENT 'для выборки записей конкретного пользователя')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат совершенные пользователем';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`space_payment_history_3` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `user_id` (user_id) COMMENT 'для выборки записей конкретного пользователя')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат совершенные пользователем';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`space_payment_history_4` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `user_id` (user_id) COMMENT 'для выборки записей конкретного пользователя')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат совершенные пользователем';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`space_payment_history_5` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `user_id` (user_id) COMMENT 'для выборки записей конкретного пользователя')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат совершенные пользователем';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`space_payment_history_6` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `user_id` (user_id) COMMENT 'для выборки записей конкретного пользователя')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат совершенные пользователем';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`space_payment_history_7` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `user_id` (user_id) COMMENT 'для выборки записей конкретного пользователя')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат совершенные пользователем';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`space_payment_history_8` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `user_id` (user_id) COMMENT 'для выборки записей конкретного пользователя')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат совершенные пользователем';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`space_payment_history_9` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `user_id` (user_id) COMMENT 'для выборки записей конкретного пользователя')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат совершенные пользователем';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`space_payment_history_10` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `user_id` (user_id) COMMENT 'для выборки записей конкретного пользователя')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат совершенные пользователем';

