use company_data;

ALTER TABLE `member_list` ADD COLUMN `left_at` INT NOT NULL DEFAULT '0' COMMENT 'время покидания пространства' AFTER `company_joined_at`;

CREATE INDEX `status.created_at` ON `hiring_request` (`status`, `created_at` DESC);
CREATE INDEX `npc_type.role.company_joined_at` ON `member_list` (`npc_type`, `role`, `company_joined_at`);

CREATE TABLE IF NOT EXISTS `member_menu` (
	`notification_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'id уведомления (auto increment)',
	`user_id` BIGINT NOT NULL COMMENT 'id администратора',
	`action_user_id` BIGINT NOT NULL COMMENT 'id пользователя, с которым произошло действие',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип действия: 10 - заявка на вступление, 20 - новый администратор, 30 - новый участник, 40 - участник удален',
	`is_unread` TINYINT NOT NULL DEFAULT 0 COMMENT '(0/1) - прочитано ли событие администратором',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
	PRIMARY KEY (`notification_id`),
	INDEX `user_id.action_user_id.type` (`user_id` ASC,`action_user_id` ASC,`type` ASC),
	INDEX `user_id.is_unread.type` (`user_id` ASC,`is_unread` ASC,`type` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'используется для хранения меню раздела участники';
