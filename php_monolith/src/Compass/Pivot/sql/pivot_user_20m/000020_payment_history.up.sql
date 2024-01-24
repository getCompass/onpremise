CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`space_payment_history_11` (
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

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`space_payment_history_12` (
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

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`space_payment_history_13` (
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

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`space_payment_history_14` (
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

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`space_payment_history_15` (
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

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`space_payment_history_16` (
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

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`space_payment_history_17` (
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

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`space_payment_history_18` (
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

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`space_payment_history_19` (
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

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`space_payment_history_20` (
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

