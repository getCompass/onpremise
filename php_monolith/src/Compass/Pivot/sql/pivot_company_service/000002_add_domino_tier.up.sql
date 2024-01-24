USE `pivot_company_service`;

ALTER TABLE `domino_registry` ADD COLUMN `hibernation_locked_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'залочено ли домино для сервисных задач' AFTER `is_company_creating_allowed`;
ALTER TABLE `domino_registry` ADD COLUMN `tier` INT(11) NOT NULL COMMENT 'уровень домино' AFTER `hibernation_locked_until`;