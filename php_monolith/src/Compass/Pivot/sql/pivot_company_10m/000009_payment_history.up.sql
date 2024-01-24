CREATE TABLE IF NOT EXISTS `pivot_company_10m`.`tariff_plan_payment_history_1` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `space_id.payment_at` (space_id, payment_at DESC) COMMENT 'для выборки списка оплат команды с убыванием по времени платежа')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат команды';

CREATE TABLE IF NOT EXISTS `pivot_company_10m`.`tariff_plan_payment_history_2` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `space_id.payment_at` (space_id, payment_at DESC) COMMENT 'для выборки списка оплат команды с убыванием по времени платежа')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат команды';

CREATE TABLE IF NOT EXISTS `pivot_company_10m`.`tariff_plan_payment_history_3` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `space_id.payment_at` (space_id, payment_at DESC) COMMENT 'для выборки списка оплат команды с убыванием по времени платежа')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат команды';

CREATE TABLE IF NOT EXISTS `pivot_company_10m`.`tariff_plan_payment_history_4` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `space_id.payment_at` (space_id, payment_at DESC) COMMENT 'для выборки списка оплат команды с убыванием по времени платежа')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат команды';

CREATE TABLE IF NOT EXISTS `pivot_company_10m`.`tariff_plan_payment_history_5` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `space_id.payment_at` (space_id, payment_at DESC) COMMENT 'для выборки списка оплат команды с убыванием по времени платежа')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат команды';

CREATE TABLE IF NOT EXISTS `pivot_company_10m`.`tariff_plan_payment_history_6` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `space_id.payment_at` (space_id, payment_at DESC) COMMENT 'для выборки списка оплат команды с убыванием по времени платежа')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат команды';

CREATE TABLE IF NOT EXISTS `pivot_company_10m`.`tariff_plan_payment_history_7` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `space_id.payment_at` (space_id, payment_at DESC) COMMENT 'для выборки списка оплат команды с убыванием по времени платежа')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат команды';

CREATE TABLE IF NOT EXISTS `pivot_company_10m`.`tariff_plan_payment_history_8` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `space_id.payment_at` (space_id, payment_at DESC) COMMENT 'для выборки списка оплат команды с убыванием по времени платежа')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат команды';

CREATE TABLE IF NOT EXISTS `pivot_company_10m`.`tariff_plan_payment_history_9` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `space_id.payment_at` (space_id, payment_at DESC) COMMENT 'для выборки списка оплат команды с убыванием по времени платежа')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат команды';

CREATE TABLE IF NOT EXISTS `pivot_company_10m`.`tariff_plan_payment_history_10` (
	`id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства',
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID плательщика',
	`tariff_plan_id` INT NOT NULL DEFAULT 0 COMMENT 'ID записи из таблицы pivot_company_{10m} . tariff_plan_history_{ceil} для связи',
	`payment_id` VARCHAR(36) NOT NULL DEFAULT 0 COMMENT 'ID платежа',
	`payment_at` INT NOT NULL DEFAULT 0 COMMENT 'Время платежа',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Время обновления записи',
	PRIMARY KEY (`id`),
        INDEX `space_id.payment_at` (space_id, payment_at DESC) COMMENT 'для выборки списка оплат команды с убыванием по времени платежа')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Хранение списка оплат команды';

