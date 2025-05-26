use `pivot_system`;

ALTER TABLE `antispam_suspect_ip` ADD COLUMN `phone_code` VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'код страны' AFTER `ip_address`;
ALTER TABLE `antispam_suspect_ip` ADD INDEX `phone_code_created_at` (`phone_code`,`created_at` ASC);