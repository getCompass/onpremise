<?php

namespace Compass\Pivot;

/**
 * Создает новую пусту доминошку
 */
class Domain_Domino_Action_Create {

    /**
     * Создает новую пусту доминошку
     *
     * @param string $domino_id
     * @param int $tier
     * @param int $is_company_creating_allowed
     * @param int $go_database_controller_port
     * @param string $url
     * @param string $database_host
     * @param string $code_host
     * @param string $go_database_controller_host
     * @throws \queryException
     * @throws cs_NotCreatedDominoTable
     */
	public static function do(string $domino_id, int $tier, int $is_company_creating_allowed, int $go_database_controller_port, string $url, string $database_host, string $code_host, string $go_database_controller_host = ""):void {

		self::_createDominoTable($domino_id);

		$extra = Domain_Domino_Entity_Registry_Extra::initExtra($go_database_controller_host, $go_database_controller_port, $url);

		$domino = new Struct_Db_PivotCompanyService_DominoRegistry(
			$domino_id,
			$code_host,
			$database_host,
			$is_company_creating_allowed,
			0,
			$tier,
			0,
			0,
			0,
			0,
			0,
			0,
			time(),
			0,
			$extra,
		);
		Gateway_Db_PivotCompanyService_DominoRegistry::insert($domino);

		// добавляем домино в список хостов, до которых можно делать rsync
		Domain_Domino_Entity_Config::addDominoHost($domino);
	}

	// создаем таблицу для доминошки
	// @long sql - код
	protected static function _createDominoTable(string $domino_id):void {

		// формируем sql для создания таблицы для портов доминошки
		$sql = "CREATE TABLE IF NOT EXISTS `port_registry_$domino_id` (
`port` INT(11) NOT NULL COMMENT 'порт',
`host` VARCHAR(255) NOT NULL COMMENT 'кастомный домен, на котором доступен порт',
`status` TINYINT(4) NOT NULL COMMENT 'статус порта',
`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип порта сервисный (10), обычный (20), резервный (30)',
`locked_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'до какого времени слот заблокирован, блокировка должна быть снята задачей, которая ее повесила',
`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления записи',
`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'Идентификтор компании, за которой закреплен порт',
`extra` MEDIUMTEXT NOT NULL COMMENT 'Дополнительные данные для порта (доступы к демону бд в зашифрованном виде)',
PRIMARY KEY (`port`),
INDEX `get_vacant` (`company_id`, `type`, `status`),
INDEX `get_by_company_id` (`company_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'таблица для хранения списка портов';

CREATE TABLE IF NOT EXISTS `company_registry_$domino_id` (
	`company_id` BIGINT(20) NOT NULL COMMENT 'id компании',
	`is_busy` TINYINT(1) NOT NULL COMMENT 'занята ли компания процессом',
	`is_hibernated` TINYINT(1) NOT NULL COMMENT 'находится ли компания в гибернации',
	`is_mysql_alive` TINYINT(1) NOT NULL  COMMENT 'живой ли демон mysql',
	`created_at` INT(11) NOT NULL COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL COMMENT 'время изменения записи',
	PRIMARY KEY (`company_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'реестр компаний';";

		$result = Gateway_Db_PivotCompanyService_Main::query($sql);
		if ($result === false) {
			throw new cs_NotCreatedDominoTable();
		}
	}
}
