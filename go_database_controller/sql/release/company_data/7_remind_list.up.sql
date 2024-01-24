use company_data;

CREATE TABLE IF NOT EXISTS `remind_list` (
    `remind_id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'идентификатор Напоминания - auto_increment',
    `type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип Напоминания (1 - сообщение в чате; 2 - сообщение в треде; 3 - родительское сообщение в тред)',
    `is_done` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'выполнено ли Напоминание (1 - да; 0 - нет)',
    `remind_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда Напоминание сработает',
    `creator_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'создатель Напоминания',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
    `recipient_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'идентификатор реципиента, на которое создано Напоминание (message_key сообщения чата/треда)',
    `data` JSON NOT NULL COMMENT 'доп. данные',
PRIMARY KEY (`remind_id`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT 'используется для хранения списка Напоминаний';