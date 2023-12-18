USE `pivot_phone`;

ALTER TABLE `phone_change_story` ADD INDEX `get_unused` (`expires_at`, `status`);
