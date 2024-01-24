use `pivot_data`;

ALTER TABLE `company_join_link_user_rel` ADD INDEX `user_id.created_at` (`user_id` ASC, `created_at` ASC);