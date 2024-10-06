USE `domino_service`;

CREATE TABLE IF NOT EXISTS `domino_service`.`port_registry` (
`port` INT(11) NOT NULL COMMENT 'порт',
`status` TINYINT(4) NOT NULL COMMENT 'статус порта',
`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип порта сервисный (10), обычный (20), резервный (30)',
`locked_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'до какого времени слот заблокирован, блокировка должна быть снята задачей, которая ее повесила',
`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления записи',
`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'Идентификтор компании, за которой закреплен порт',
`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'Дополнительные данные для порта (доступы к демону бд в зашифрованном виде)',
PRIMARY KEY (`port`),
INDEX `get_by_company_id` (`company_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'таблица для хранения списка портов';