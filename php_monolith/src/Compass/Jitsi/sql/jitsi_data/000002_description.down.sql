use `jitsi_data`;

ALTER TABLE `jitsi_data`.`conference_list` DROP COLUMN `conference_url_custom_name`;
ALTER TABLE `jitsi_data`.`conference_list` DROP COLUMN `description`;

DROP TABLE IF EXISTS `jitsi_data`.`permanent_conference_list`;