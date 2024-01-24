USE `pivot_user_20m`;

CREATE TABLE IF NOT EXISTS `premium_status_observe` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'ид записи',
    `observe_at` INT(11) NOT NULL COMMENT 'дата observe действия',
    `action` INT(11) NOT NULL COMMENT 'действие, которое должен выполнить обсервер',
    `created_at` INT(11) NOT NULL COMMENT 'дата создания записи',
    `user_id` BIGINT(20) NOT NULL COMMENT 'ид пользователя (фактически ид премиума)',
    PRIMARY KEY (`id`),
    INDEX `get_for_observe` (`observe_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'сервисная таблица для observe премиум-статусов';
