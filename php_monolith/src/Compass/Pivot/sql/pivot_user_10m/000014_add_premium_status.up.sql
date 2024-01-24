USE `pivot_user_10m`;

CREATE TABLE IF NOT EXISTS `premium_status_1` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`need_block_if_inactive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг блокировки пре неактивном премиуме',
    `free_active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия бесплатного доступа',
    `active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия платного доступа',
	`created_at` INT(11) NOT NULL COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`last_prolongation_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего продления',
	`last_prolongation_duration` INT(11) NOT NULL DEFAULT 0 COMMENT 'длительность последнего продления',
	`last_prolongation_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, продлившего премиум доступ',
	`last_prolongation_payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор последнего платежа',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
	PRIMARY KEY (`user_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список текущих премиум-статусов пользователей';

CREATE TABLE IF NOT EXISTS `premium_status_2` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `need_block_if_inactive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг блокировки пре неактивном премиуме',
    `free_active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия бесплатного доступа',
    `active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия платного доступа',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `last_prolongation_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего продления',
    `last_prolongation_duration` INT(11) NOT NULL DEFAULT 0 COMMENT 'длительность последнего продления',
    `last_prolongation_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `last_prolongation_payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор последнего платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`user_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список текущих премиум-статусов пользователей';

CREATE TABLE IF NOT EXISTS `premium_status_3` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `need_block_if_inactive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг блокировки пре неактивном премиуме',
    `free_active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия бесплатного доступа',
    `active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия платного доступа',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `last_prolongation_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего продления',
    `last_prolongation_duration` INT(11) NOT NULL DEFAULT 0 COMMENT 'длительность последнего продления',
    `last_prolongation_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `last_prolongation_payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор последнего платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`user_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список текущих премиум-статусов пользователей';

CREATE TABLE IF NOT EXISTS `premium_status_4` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `need_block_if_inactive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг блокировки пре неактивном премиуме',
    `free_active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия бесплатного доступа',
    `active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия платного доступа',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `last_prolongation_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего продления',
    `last_prolongation_duration` INT(11) NOT NULL DEFAULT 0 COMMENT 'длительность последнего продления',
    `last_prolongation_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `last_prolongation_payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор последнего платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`user_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список текущих премиум-статусов пользователей';

CREATE TABLE IF NOT EXISTS `premium_status_5` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `need_block_if_inactive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг блокировки пре неактивном премиуме',
    `free_active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия бесплатного доступа',
    `active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия платного доступа',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `last_prolongation_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего продления',
    `last_prolongation_duration` INT(11) NOT NULL DEFAULT 0 COMMENT 'длительность последнего продления',
    `last_prolongation_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `last_prolongation_payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор последнего платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`user_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список текущих премиум-статусов пользователей';

CREATE TABLE IF NOT EXISTS `premium_status_6` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `need_block_if_inactive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг блокировки пре неактивном премиуме',
    `free_active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия бесплатного доступа',
    `active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия платного доступа',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `last_prolongation_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего продления',
    `last_prolongation_duration` INT(11) NOT NULL DEFAULT 0 COMMENT 'длительность последнего продления',
    `last_prolongation_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `last_prolongation_payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор последнего платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`user_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список текущих премиум-статусов пользователей';

CREATE TABLE IF NOT EXISTS `premium_status_7` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `need_block_if_inactive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг блокировки пре неактивном премиуме',
    `free_active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия бесплатного доступа',
    `active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия платного доступа',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `last_prolongation_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего продления',
    `last_prolongation_duration` INT(11) NOT NULL DEFAULT 0 COMMENT 'длительность последнего продления',
    `last_prolongation_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `last_prolongation_payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор последнего платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`user_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список текущих премиум-статусов пользователей';

CREATE TABLE IF NOT EXISTS `premium_status_8` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `need_block_if_inactive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг блокировки пре неактивном премиуме',
    `free_active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия бесплатного доступа',
    `active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия платного доступа',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `last_prolongation_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего продления',
    `last_prolongation_duration` INT(11) NOT NULL DEFAULT 0 COMMENT 'длительность последнего продления',
    `last_prolongation_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `last_prolongation_payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор последнего платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`user_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список текущих премиум-статусов пользователей';

CREATE TABLE IF NOT EXISTS `premium_status_9` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `need_block_if_inactive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг блокировки пре неактивном премиуме',
    `free_active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия бесплатного доступа',
    `active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия платного доступа',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `last_prolongation_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего продления',
    `last_prolongation_duration` INT(11) NOT NULL DEFAULT 0 COMMENT 'длительность последнего продления',
    `last_prolongation_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `last_prolongation_payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор последнего платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`user_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список текущих премиум-статусов пользователей';

CREATE TABLE IF NOT EXISTS `premium_status_10` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `need_block_if_inactive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг блокировки пре неактивном премиуме',
    `free_active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия бесплатного доступа',
    `active_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'время действия платного доступа',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `last_prolongation_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего продления',
    `last_prolongation_duration` INT(11) NOT NULL DEFAULT 0 COMMENT 'длительность последнего продления',
    `last_prolongation_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `last_prolongation_payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор последнего платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`user_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список текущих премиум-статусов пользователей';

CREATE TABLE IF NOT EXISTS `premium_prolongation_history_1` (
    `id` BIGINT(20) NOT NULL COMMENT 'инкрементальный идентификатор',
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `action` INT(11) NOT NULL COMMENT 'тип совершенного действия',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `duration` INT(11) NOT NULL COMMENT 'длительность продления',
    `active_till` INT(11) NOT NULL COMMENT 'обновленная дата действия премиума',
    `doer_user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`id`),
    INDEX `get_by_user_id` (`user_id`, `created_at` DESC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'история продления премиум-статуса';

CREATE TABLE IF NOT EXISTS `premium_prolongation_history_2` (
    `id` BIGINT(20) NOT NULL COMMENT 'инкрементальный идентификатор',
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `action` INT(11) NOT NULL COMMENT 'тип совершенного действия',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `duration` INT(11) NOT NULL COMMENT 'длительность продления',
    `active_till` INT(11) NOT NULL COMMENT 'обновленная дата действия премиума',
    `doer_user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`id`),
    INDEX `get_by_user_id` (`user_id`, `created_at` DESC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'история продления премиум-статуса';

CREATE TABLE IF NOT EXISTS `premium_prolongation_history_3` (
    `id` BIGINT(20) NOT NULL COMMENT 'инкрементальный идентификатор',
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `action` INT(11) NOT NULL COMMENT 'тип совершенного действия',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `duration` INT(11) NOT NULL COMMENT 'длительность продления',
    `active_till` INT(11) NOT NULL COMMENT 'обновленная дата действия премиума',
    `doer_user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`id`),
    INDEX `get_by_user_id` (`user_id`, `created_at` DESC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'история продления премиум-статуса';

CREATE TABLE IF NOT EXISTS `premium_prolongation_history_4` (
    `id` BIGINT(20) NOT NULL COMMENT 'инкрементальный идентификатор',
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `action` INT(11) NOT NULL COMMENT 'тип совершенного действия',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `duration` INT(11) NOT NULL COMMENT 'длительность продления',
    `active_till` INT(11) NOT NULL COMMENT 'обновленная дата действия премиума',
    `doer_user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`id`),
    INDEX `get_by_user_id` (`user_id`, `created_at` DESC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'история продления премиум-статуса';

CREATE TABLE IF NOT EXISTS `premium_prolongation_history_5` (
    `id` BIGINT(20) NOT NULL COMMENT 'инкрементальный идентификатор',
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `action` INT(11) NOT NULL COMMENT 'тип совершенного действия',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `duration` INT(11) NOT NULL COMMENT 'длительность продления',
    `active_till` INT(11) NOT NULL COMMENT 'обновленная дата действия премиума',
    `doer_user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`id`),
    INDEX `get_by_user_id` (`user_id`, `created_at` DESC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'история продления премиум-статуса';

CREATE TABLE IF NOT EXISTS `premium_prolongation_history_6` (
    `id` BIGINT(20) NOT NULL COMMENT 'инкрементальный идентификатор',
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `action` INT(11) NOT NULL COMMENT 'тип совершенного действия',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `duration` INT(11) NOT NULL COMMENT 'длительность продления',
    `active_till` INT(11) NOT NULL COMMENT 'обновленная дата действия премиума',
    `doer_user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`id`),
    INDEX `get_by_user_id` (`user_id`, `created_at` DESC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'история продления премиум-статуса';

CREATE TABLE IF NOT EXISTS `premium_prolongation_history_7` (
    `id` BIGINT(20) NOT NULL COMMENT 'инкрементальный идентификатор',
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `action` INT(11) NOT NULL COMMENT 'тип совершенного действия',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `duration` INT(11) NOT NULL COMMENT 'длительность продления',
    `active_till` INT(11) NOT NULL COMMENT 'обновленная дата действия премиума',
    `doer_user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`id`),
    INDEX `get_by_user_id` (`user_id`, `created_at` DESC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'история продления премиум-статуса';

CREATE TABLE IF NOT EXISTS `premium_prolongation_history_8` (
    `id` BIGINT(20) NOT NULL COMMENT 'инкрементальный идентификатор',
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `action` INT(11) NOT NULL COMMENT 'тип совершенного действия',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `duration` INT(11) NOT NULL COMMENT 'длительность продления',
    `active_till` INT(11) NOT NULL COMMENT 'обновленная дата действия премиума',
    `doer_user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`id`),
    INDEX `get_by_user_id` (`user_id`, `created_at` DESC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'история продления премиум-статуса';

CREATE TABLE IF NOT EXISTS `premium_prolongation_history_9` (
    `id` BIGINT(20) NOT NULL COMMENT 'инкрементальный идентификатор',
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `action` INT(11) NOT NULL COMMENT 'тип совершенного действия',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `duration` INT(11) NOT NULL COMMENT 'длительность продления',
    `active_till` INT(11) NOT NULL COMMENT 'обновленная дата действия премиума',
    `doer_user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`id`),
    INDEX `get_by_user_id` (`user_id`, `created_at` DESC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'история продления премиум-статуса';

CREATE TABLE IF NOT EXISTS `premium_prolongation_history_10` (
    `id` BIGINT(20) NOT NULL COMMENT 'инкрементальный идентификатор',
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `action` INT(11) NOT NULL COMMENT 'тип совершенного действия',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    `duration` INT(11) NOT NULL COMMENT 'длительность продления',
    `active_till` INT(11) NOT NULL COMMENT 'обновленная дата действия премиума',
    `doer_user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя, продлившего премиум доступ',
    `payment_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор платежа',
    `extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'поле для дополнительных данных',
    PRIMARY KEY (`id`),
    INDEX `get_by_user_id` (`user_id`, `created_at` DESC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'история продления премиум-статуса';

CREATE TABLE IF NOT EXISTS `used_premium_promo_product_1` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `label` VARCHAR(128) NOT NULL COMMENT 'идентификатор активированного товара',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `label`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список использованных промо-продуктов';

CREATE TABLE IF NOT EXISTS `used_premium_promo_product_2` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `label` VARCHAR(128) NOT NULL COMMENT 'идентификатор активированного товара',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `label`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список использованных промо-продуктов';

CREATE TABLE IF NOT EXISTS `used_premium_promo_product_3` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `label` VARCHAR(128) NOT NULL COMMENT 'идентификатор активированного товара',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `label`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список использованных промо-продуктов';

CREATE TABLE IF NOT EXISTS `used_premium_promo_product_4` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `label` VARCHAR(128) NOT NULL COMMENT 'идентификатор активированного товара',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `label`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список использованных промо-продуктов';

CREATE TABLE IF NOT EXISTS `used_premium_promo_product_5` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `label` VARCHAR(128) NOT NULL COMMENT 'идентификатор активированного товара',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `label`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список использованных промо-продуктов';

CREATE TABLE IF NOT EXISTS `used_premium_promo_product_6` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `label` VARCHAR(128) NOT NULL COMMENT 'идентификатор активированного товара',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `label`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список использованных промо-продуктов';

CREATE TABLE IF NOT EXISTS `used_premium_promo_product_7` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `label` VARCHAR(128) NOT NULL COMMENT 'идентификатор активированного товара',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `label`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список использованных промо-продуктов';

CREATE TABLE IF NOT EXISTS `used_premium_promo_product_8` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `label` VARCHAR(128) NOT NULL COMMENT 'идентификатор активированного товара',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `label`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список использованных промо-продуктов';

CREATE TABLE IF NOT EXISTS `used_premium_promo_product_9` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `label` VARCHAR(128) NOT NULL COMMENT 'идентификатор активированного товара',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `label`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список использованных промо-продуктов';

CREATE TABLE IF NOT EXISTS `used_premium_promo_product_10` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
    `label` VARCHAR(128) NOT NULL COMMENT 'идентификатор активированного товара',
    `created_at` INT(11) NOT NULL COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `label`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'список использованных промо-продуктов';