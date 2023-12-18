USE `userbot_service`;

CREATE TABLE IF NOT EXISTS `antispam_ip` (
    `ip_address`  VARCHAR(45)   NOT NULL DEFAULT '' COMMENT 'ip-адрес',
    `key`         VARCHAR(455)  NOT NULL DEFAULT '' COMMENT 'ключ блокировки',
    `count`       INT(11)       NOT NULL DEFAULT 0 COMMENT 'количество набранных значений для блокировки',
    `expire`      INT(11)       NOT NULL DEFAULT 0 COMMENT 'время, когда блокировка истекает',
    PRIMARY KEY (`ip_address`, `key`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'таблица для блокировок по ip-адресу';

CREATE TABLE IF NOT EXISTS `datastore` (
    `key`    VARCHAR(255)  NOT NULL DEFAULT '' COMMENT 'ключ',
    `extra`  JSON          NOT NULL COMMENT 'произвольный JSON массив',
    PRIMARY KEY (`key`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'таблица с системными значениями для фреймворка\nнужна для работы крона general.php, а также по необходимости в нее могут писать другие модули';

CREATE TABLE IF NOT EXISTS `unit_test` (
    `key`      VARCHAR(32)  NOT NULL DEFAULT '' COMMENT 'Идентификатор',
    `int_row`  INT(11)      NOT NULL DEFAULT '0' COMMENT 'поле для тестирования значений типа int',
    `extra`    JSON         NOT NULL COMMENT 'Поле для тестирования json значений',
    PRIMARY KEY (`key`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'таблица для юнит тестов типа smoke(проверяет выполнение основных операций с mysql)';