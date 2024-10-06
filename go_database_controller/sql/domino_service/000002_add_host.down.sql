USE `domino_service`;

ALTER TABLE `domino_service`.`port_registry` DROP INDEX `uniq`;
ALTER TABLE `domino_service`.`port_registry` DROP COLUMN `host`;