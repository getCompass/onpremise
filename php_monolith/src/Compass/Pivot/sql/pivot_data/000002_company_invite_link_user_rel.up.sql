USE `pivot_data`;

CREATE TABLE IF NOT EXISTS `company_invite_link_user_rel` (
  `invite_link_uniq` VARCHAR(12) NOT NULL DEFAULT '' COMMENT 'идентификатор ссылки-инвайта',
  `user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который принял приглашение',
  `company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
  `entry_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id входа в компанию',
  `status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус перехода по ссылке',
  `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда было принято приглашение',
  `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда была обновлена запись',
PRIMARY KEY (`invite_link_uniq`, `user_id`, `company_id`),
INDEX `entry_user_company` (`entry_id`, `user_id`, `company_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT 'таблица которая хранит данные по каждому принятому инвайту-ссылке';