USE `pivot_company_10m`;

-- переименовываем поля обратно
ALTER TABLE company_list_1 CHANGE COLUMN created_by_user_id creator_user_id BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'создатель компании';
ALTER TABLE company_list_2 CHANGE COLUMN created_by_user_id creator_user_id BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'создатель компании';
ALTER TABLE company_list_3 CHANGE COLUMN created_by_user_id creator_user_id BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'создатель компании';
ALTER TABLE company_list_4 CHANGE COLUMN created_by_user_id creator_user_id BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'создатель компании';
ALTER TABLE company_list_5 CHANGE COLUMN created_by_user_id creator_user_id BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'создатель компании';
ALTER TABLE company_list_6 CHANGE COLUMN created_by_user_id creator_user_id BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'создатель компании';
ALTER TABLE company_list_7 CHANGE COLUMN created_by_user_id creator_user_id BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'создатель компании';
ALTER TABLE company_list_8 CHANGE COLUMN created_by_user_id creator_user_id BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'создатель компании';
ALTER TABLE company_list_9 CHANGE COLUMN created_by_user_id creator_user_id BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'создатель компании';
ALTER TABLE company_list_10 CHANGE COLUMN created_by_user_id creator_user_id BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'создатель компании';

