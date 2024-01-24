/* @formatter:off */

CREATE TABLE IF NOT EXISTS `call_ip_last_connection_issue` (
	`ip_address_int` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IP адрес в integer формате',
	`last_happened_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда в последний раз случалась проблема с IP адресом',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была создана запись',
	PRIMARY KEY (`ip_address_int`))
        ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'храним временную метку о последней проблеме в соединении для IP адреса';
