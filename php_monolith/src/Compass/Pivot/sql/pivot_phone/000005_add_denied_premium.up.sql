USE `pivot_phone`;

ALTER TABLE `phone_uniq_list_1` ADD COLUMN `binding_count` INT(11) NOT NULL DEFAULT 1 COMMENT 'количество связей телефона с пользователем' AFTER `user_id`;
ALTER TABLE `phone_uniq_list_1` ADD COLUMN `last_binding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней привязки пользователя к номеру' AFTER `binding_count`;
ALTER TABLE `phone_uniq_list_1` ADD COLUMN `last_unbinding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней отвязки номера от пользователя' AFTER `last_binding_at`;
ALTER TABLE `phone_uniq_list_1` ADD COLUMN `previous_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список пользователей, пользовавшихся этм номером' AFTER `updated_at`;
ALTER TABLE `phone_uniq_list_1` ADD INDEX `get_reused` (`binding_count`);

ALTER TABLE `phone_uniq_list_2` ADD COLUMN `binding_count` INT(11) NOT NULL DEFAULT 1 COMMENT 'количество связей телефона с пользователем' AFTER `user_id`;
ALTER TABLE `phone_uniq_list_2` ADD COLUMN `last_binding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней привязки пользователя к номеру' AFTER `binding_count`;
ALTER TABLE `phone_uniq_list_2` ADD COLUMN `last_unbinding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней отвязки номера от пользователя' AFTER `last_binding_at`;
ALTER TABLE `phone_uniq_list_2` ADD COLUMN `previous_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список пользователей, пользовавшихся этм номером' AFTER `updated_at`;
ALTER TABLE `phone_uniq_list_2` ADD INDEX `get_reused` (`binding_count`);

ALTER TABLE `phone_uniq_list_3` ADD COLUMN `binding_count` INT(11) NOT NULL DEFAULT 1 COMMENT 'количество связей телефона с пользователем' AFTER `user_id`;
ALTER TABLE `phone_uniq_list_3` ADD COLUMN `last_binding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней привязки пользователя к номеру' AFTER `binding_count`;
ALTER TABLE `phone_uniq_list_3` ADD COLUMN `last_unbinding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней отвязки номера от пользователя' AFTER `last_binding_at`;
ALTER TABLE `phone_uniq_list_3` ADD COLUMN `previous_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список пользователей, пользовавшихся этм номером' AFTER `updated_at`;
ALTER TABLE `phone_uniq_list_3` ADD INDEX `get_reused` (`binding_count`);

ALTER TABLE `phone_uniq_list_4` ADD COLUMN `binding_count` INT(11) NOT NULL DEFAULT 1 COMMENT 'количество связей телефона с пользователем' AFTER `user_id`;
ALTER TABLE `phone_uniq_list_4` ADD COLUMN `last_binding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней привязки пользователя к номеру' AFTER `binding_count`;
ALTER TABLE `phone_uniq_list_4` ADD COLUMN `last_unbinding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней отвязки номера от пользователя' AFTER `last_binding_at`;
ALTER TABLE `phone_uniq_list_4` ADD COLUMN `previous_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список пользователей, пользовавшихся этм номером' AFTER `updated_at`;
ALTER TABLE `phone_uniq_list_4` ADD INDEX `get_reused` (`binding_count`);

ALTER TABLE `phone_uniq_list_5` ADD COLUMN `binding_count` INT(11) NOT NULL DEFAULT 1 COMMENT 'количество связей телефона с пользователем' AFTER `user_id`;
ALTER TABLE `phone_uniq_list_5` ADD COLUMN `last_binding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней привязки пользователя к номеру' AFTER `binding_count`;
ALTER TABLE `phone_uniq_list_5` ADD COLUMN `last_unbinding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней отвязки номера от пользователя' AFTER `last_binding_at`;
ALTER TABLE `phone_uniq_list_5` ADD COLUMN `previous_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список пользователей, пользовавшихся этм номером' AFTER `updated_at`;
ALTER TABLE `phone_uniq_list_5` ADD INDEX `get_reused` (`binding_count`);

ALTER TABLE `phone_uniq_list_6` ADD COLUMN `binding_count` INT(11) NOT NULL DEFAULT 1 COMMENT 'количество связей телефона с пользователем' AFTER `user_id`;
ALTER TABLE `phone_uniq_list_6` ADD COLUMN `last_binding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней привязки пользователя к номеру' AFTER `binding_count`;
ALTER TABLE `phone_uniq_list_6` ADD COLUMN `last_unbinding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней отвязки номера от пользователя' AFTER `last_binding_at`;
ALTER TABLE `phone_uniq_list_6` ADD COLUMN `previous_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список пользователей, пользовавшихся этм номером' AFTER `updated_at`;
ALTER TABLE `phone_uniq_list_6` ADD INDEX `get_reused` (`binding_count`);

ALTER TABLE `phone_uniq_list_7` ADD COLUMN `binding_count` INT(11) NOT NULL DEFAULT 1 COMMENT 'количество связей телефона с пользователем' AFTER `user_id`;
ALTER TABLE `phone_uniq_list_7` ADD COLUMN `last_binding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней привязки пользователя к номеру' AFTER `binding_count`;
ALTER TABLE `phone_uniq_list_7` ADD COLUMN `last_unbinding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней отвязки номера от пользователя' AFTER `last_binding_at`;
ALTER TABLE `phone_uniq_list_7` ADD COLUMN `previous_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список пользователей, пользовавшихся этм номером' AFTER `updated_at`;
ALTER TABLE `phone_uniq_list_7` ADD INDEX `get_reused` (`binding_count`);

ALTER TABLE `phone_uniq_list_8` ADD COLUMN `binding_count` INT(11) NOT NULL DEFAULT 1 COMMENT 'количество связей телефона с пользователем' AFTER `user_id`;
ALTER TABLE `phone_uniq_list_8` ADD COLUMN `last_binding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней привязки пользователя к номеру' AFTER `binding_count`;
ALTER TABLE `phone_uniq_list_8` ADD COLUMN `last_unbinding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней отвязки номера от пользователя' AFTER `last_binding_at`;
ALTER TABLE `phone_uniq_list_8` ADD COLUMN `previous_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список пользователей, пользовавшихся этм номером' AFTER `updated_at`;
ALTER TABLE `phone_uniq_list_8` ADD INDEX `get_reused` (`binding_count`);

ALTER TABLE `phone_uniq_list_9` ADD COLUMN `binding_count` INT(11) NOT NULL DEFAULT 1 COMMENT 'количество связей телефона с пользователем' AFTER `user_id`;
ALTER TABLE `phone_uniq_list_9` ADD COLUMN `last_binding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней привязки пользователя к номеру' AFTER `binding_count`;
ALTER TABLE `phone_uniq_list_9` ADD COLUMN `last_unbinding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней отвязки номера от пользователя' AFTER `last_binding_at`;
ALTER TABLE `phone_uniq_list_9` ADD COLUMN `previous_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список пользователей, пользовавшихся этм номером' AFTER `updated_at`;
ALTER TABLE `phone_uniq_list_9` ADD INDEX `get_reused` (`binding_count`);

ALTER TABLE `phone_uniq_list_0` ADD COLUMN `binding_count` INT(11) NOT NULL DEFAULT 1 COMMENT 'количество связей телефона с пользователем' AFTER `user_id`;
ALTER TABLE `phone_uniq_list_0` ADD COLUMN `last_binding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней привязки пользователя к номеру' AFTER `binding_count`;
ALTER TABLE `phone_uniq_list_0` ADD COLUMN `last_unbinding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней отвязки номера от пользователя' AFTER `last_binding_at`;
ALTER TABLE `phone_uniq_list_0` ADD COLUMN `previous_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список пользователей, пользовавшихся этм номером' AFTER `updated_at`;
ALTER TABLE `phone_uniq_list_0` ADD INDEX `get_reused` (`binding_count`);

ALTER TABLE `phone_uniq_list_a` ADD COLUMN `binding_count` INT(11) NOT NULL DEFAULT 1 COMMENT 'количество связей телефона с пользователем' AFTER `user_id`;
ALTER TABLE `phone_uniq_list_a` ADD COLUMN `last_binding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней привязки пользователя к номеру' AFTER `binding_count`;
ALTER TABLE `phone_uniq_list_a` ADD COLUMN `last_unbinding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней отвязки номера от пользователя' AFTER `last_binding_at`;
ALTER TABLE `phone_uniq_list_a` ADD COLUMN `previous_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список пользователей, пользовавшихся этм номером' AFTER `updated_at`;
ALTER TABLE `phone_uniq_list_a` ADD INDEX `get_reused` (`binding_count`);

ALTER TABLE `phone_uniq_list_b` ADD COLUMN `binding_count` INT(11) NOT NULL DEFAULT 1 COMMENT 'количество связей телефона с пользователем' AFTER `user_id`;
ALTER TABLE `phone_uniq_list_b` ADD COLUMN `last_binding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней привязки пользователя к номеру' AFTER `binding_count`;
ALTER TABLE `phone_uniq_list_b` ADD COLUMN `last_unbinding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней отвязки номера от пользователя' AFTER `last_binding_at`;
ALTER TABLE `phone_uniq_list_b` ADD COLUMN `previous_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список пользователей, пользовавшихся этм номером' AFTER `updated_at`;
ALTER TABLE `phone_uniq_list_b` ADD INDEX `get_reused` (`binding_count`);

ALTER TABLE `phone_uniq_list_c` ADD COLUMN `binding_count` INT(11) NOT NULL DEFAULT 1 COMMENT 'количество связей телефона с пользователем' AFTER `user_id`;
ALTER TABLE `phone_uniq_list_c` ADD COLUMN `last_binding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней привязки пользователя к номеру' AFTER `binding_count`;
ALTER TABLE `phone_uniq_list_c` ADD COLUMN `last_unbinding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней отвязки номера от пользователя' AFTER `last_binding_at`;
ALTER TABLE `phone_uniq_list_c` ADD COLUMN `previous_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список пользователей, пользовавшихся этм номером' AFTER `updated_at`;
ALTER TABLE `phone_uniq_list_c` ADD INDEX `get_reused` (`binding_count`);

ALTER TABLE `phone_uniq_list_d` ADD COLUMN `binding_count` INT(11) NOT NULL DEFAULT 1 COMMENT 'количество связей телефона с пользователем' AFTER `user_id`;
ALTER TABLE `phone_uniq_list_d` ADD COLUMN `last_binding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней привязки пользователя к номеру' AFTER `binding_count`;
ALTER TABLE `phone_uniq_list_d` ADD COLUMN `last_unbinding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней отвязки номера от пользователя' AFTER `last_binding_at`;
ALTER TABLE `phone_uniq_list_d` ADD COLUMN `previous_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список пользователей, пользовавшихся этм номером' AFTER `updated_at`;
ALTER TABLE `phone_uniq_list_d` ADD INDEX `get_reused` (`binding_count`);

ALTER TABLE `phone_uniq_list_e` ADD COLUMN `binding_count` INT(11) NOT NULL DEFAULT 1 COMMENT 'количество связей телефона с пользователем' AFTER `user_id`;
ALTER TABLE `phone_uniq_list_e` ADD COLUMN `last_binding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней привязки пользователя к номеру' AFTER `binding_count`;
ALTER TABLE `phone_uniq_list_e` ADD COLUMN `last_unbinding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней отвязки номера от пользователя' AFTER `last_binding_at`;
ALTER TABLE `phone_uniq_list_e` ADD COLUMN `previous_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список пользователей, пользовавшихся этм номером' AFTER `updated_at`;
ALTER TABLE `phone_uniq_list_e` ADD INDEX `get_reused` (`binding_count`);

ALTER TABLE `phone_uniq_list_f` ADD COLUMN `binding_count` INT(11) NOT NULL DEFAULT 1 COMMENT 'количество связей телефона с пользователем' AFTER `user_id`;
ALTER TABLE `phone_uniq_list_f` ADD COLUMN `last_binding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней привязки пользователя к номеру' AFTER `binding_count`;
ALTER TABLE `phone_uniq_list_f` ADD COLUMN `last_unbinding_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последней отвязки номера от пользователя' AFTER `last_binding_at`;
ALTER TABLE `phone_uniq_list_f` ADD COLUMN `previous_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список пользователей, пользовавшихся этм номером' AFTER `updated_at`;
ALTER TABLE `phone_uniq_list_f` ADD INDEX `get_reused` (`binding_count`);
