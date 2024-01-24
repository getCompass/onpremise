USE `pivot_user_10m`;

CREATE INDEX `company_id_status` ON `user_company_list_1` (`company_id`, `status`);