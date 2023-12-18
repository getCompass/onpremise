use `pivot_data`;

ALTER TABLE `company_join_link_user_rel` ADD INDEX `company_id.status` (`company_id`, `status`);