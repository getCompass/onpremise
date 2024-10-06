USE `domino_service`;

ALTER TABLE `domino_service`.`port_registry` ADD COLUMN `host` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'кастомный домен, на котором доступен порт' AFTER `port`;
ALTER TABLE `domino_service`.`port_registry` ADD INDEX `uniq` (`port`, `host`);