use company_data;

ALTER TABLE `rating_member_day_list` ADD INDEX `day_start_is_disabled_alias` (`day_start` ASC, `is_disabled_alias` ASC);
