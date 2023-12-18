USE `pivot_company_10m`;

ALTER TABLE `company_list_1` ADD COLUMN `domino_id` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'id домино на котором находится компания' AFTER `partner_id`;
ALTER TABLE `company_list_2` ADD COLUMN `domino_id` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'id домино на котором находится компания' AFTER `partner_id`;
ALTER TABLE `company_list_3` ADD COLUMN `domino_id` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'id домино на котором находится компания' AFTER `partner_id`;
ALTER TABLE `company_list_4` ADD COLUMN `domino_id` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'id домино на котором находится компания' AFTER `partner_id`;
ALTER TABLE `company_list_5` ADD COLUMN `domino_id` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'id домино на котором находится компания' AFTER `partner_id`;
ALTER TABLE `company_list_6` ADD COLUMN `domino_id` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'id домино на котором находится компания' AFTER `partner_id`;
ALTER TABLE `company_list_7` ADD COLUMN `domino_id` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'id домино на котором находится компания' AFTER `partner_id`;
ALTER TABLE `company_list_8` ADD COLUMN `domino_id` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'id домино на котором находится компания' AFTER `partner_id`;
ALTER TABLE `company_list_9` ADD COLUMN `domino_id` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'id домино на котором находится компания' AFTER `partner_id`;
ALTER TABLE `company_list_10` ADD COLUMN `domino_id` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'id домино на котором находится компания' AFTER `partner_id`;