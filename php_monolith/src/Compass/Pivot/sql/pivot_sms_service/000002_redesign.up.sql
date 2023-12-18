USE `pivot_sms_service` ;

ALTER TABLE `observe_provider_list_task` RENAME `observer_provider`;
ALTER TABLE `observer_provider` DROP INDEX cron_sms_provider_observer;
ALTER TABLE `observer_provider` ADD INDEX need_work (`need_work`);
ALTER TABLE `observer_provider` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());

ALTER TABLE `send_queue` CHANGE COLUMN `error_count` `error_count` INT(11);
ALTER TABLE `send_queue` CHANGE COLUMN `task_expire_at` `expires_at` INT(11);
ALTER TABLE `send_queue` CHANGE COLUMN `phone_number` `phone_number` VARCHAR(45);
ALTER TABLE `send_queue` CHANGE COLUMN `provider_id` `provider_id` VARCHAR(255) AFTER `phone_number`;
ALTER TABLE `send_queue` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());

ALTER TABLE `provider_list` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());

/* Второй этап редизайна, в этой базе не должно быть этой таблицы DROP TABLE IF EXISTS `send_history`; */