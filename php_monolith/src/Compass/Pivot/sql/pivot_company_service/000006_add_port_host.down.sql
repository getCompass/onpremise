USE `pivot_company_service`;

ALTER TABLE `port_registry` DROP COLUMN `host`;
ALTER TABLE `port_registry` DROP INDEX `uniq`;