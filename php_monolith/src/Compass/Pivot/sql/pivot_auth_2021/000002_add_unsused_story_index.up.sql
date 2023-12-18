USE `pivot_auth_2021`;

ALTER TABLE `2fa_list_1`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `2fa_list_2`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `2fa_list_3`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `2fa_list_4`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `2fa_list_5`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `2fa_list_6`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `2fa_list_7`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `2fa_list_8`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `2fa_list_9`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `2fa_list_10` ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `2fa_list_11` ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `2fa_list_12` ADD INDEX `get_unused` (`expires_at`, `is_success`);

ALTER TABLE `auth_list_1`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `auth_list_2`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `auth_list_3`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `auth_list_4`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `auth_list_5`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `auth_list_6`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `auth_list_7`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `auth_list_8`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `auth_list_9`  ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `auth_list_10` ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `auth_list_11` ADD INDEX `get_unused` (`expires_at`, `is_success`);
ALTER TABLE `auth_list_12` ADD INDEX `get_unused` (`expires_at`, `is_success`);

