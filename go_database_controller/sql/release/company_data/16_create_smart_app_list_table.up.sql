use company_data;

CREATE TABLE IF NOT EXISTS `smart_app_list` (
  `smart_app_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'автоинкрементальный идентификатор smart app в рамках компании',
  `catalog_item_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор приложения, если оно был создано из каталога',
  `creator_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, который создал smart app',
  `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
  `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
  `smart_app_uniq_name` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'уникальное имя smart app в рамках компании',
  `extra` JSON NOT NULL COMMENT 'доп. данные',
PRIMARY KEY (`smart_app_id`),
INDEX `creator_user_id` (`creator_user_id` ASC) COMMENT 'Индекс для выборки по id создателя',
INDEX `smart_app_uniq_name` (`smart_app_uniq_name` ASC) COMMENT 'Индекс для выборки по уникальному имени')
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT 'используется для хранения списка приложений в компании';

CREATE TABLE IF NOT EXISTS `smart_app_user_rel` (
  `smart_app_id` INT(11) NOT NULL COMMENT 'идентификатор smart app в рамках компании',
  `user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя, который создал smart app',
  `status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус приложения',
  `deleted_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда приложение было удалено',
  `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
  `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
  `extra` JSON NOT NULL COMMENT 'доп. данные',
PRIMARY KEY (`smart_app_id`, `user_id`),
INDEX `user_id.status` (`user_id` ASC, `status` ASC) COMMENT 'Индекс для выборки по стаусу и id создателя')
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT 'используется для хранения списка приложений созданных пользователем';