USE `pivot_company_service`;

ALTER TABLE `port_registry` ADD COLUMN `host` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'кастомный домен, на котором доступен порт' AFTER `port`;
ALTER TABLE `port_registry` ADD INDEX `uniq` (`port`, `host`);