<?php

namespace Compass\Pivot;

use JetBrains\PhpStorm\ArrayShape;

/**
 * сущность реестра портов в доменошке
 */
class Domain_Domino_Entity_Port_Registry {

	public const STATUS_VOID    = 10; // порт готов обслуживать компании, не содержит в себе активных компаний
	public const STATUS_ACTIVE  = 20; // порт занят активной компаний и с ним нельзя проводить никаких действий
	public const STATUS_LOCKED  = 30; // порт занят для какой-то сервисной задачи (например прогрев, пока статус еще не активен, но его нельзя брать для других компаний);
	public const STATUS_INVALID = 90; // во время работы произошла ошибка и состояние порта неизвестно, переход в этот статус возможен из любого другого состояния

	public const TYPE_SERVICE = 10; // сервисный порт
	public const TYPE_COMMON  = 20; // обычный порт
	public const TYPE_RESERVE = 30; // резервный порт

	protected const _EXTRA_VERSION = 1; // версия упаковщика
	protected const _EXTRA_SCHEMA  = [  // схема extra

		1 => [
			"encrypted_mysql_user" => "",
			"encrypted_mysql_pass" => "",
		],
	];

	/**
	 * Создать новую структуру для extra
	 *
	 * @param string $encrypted_mysql_user
	 * @param string $encrypted_mysql_pass
	 *
	 * @return array
	 */
	#[ArrayShape(["version" => "int", "extra" => "int[]"])]
	public static function initExtra(string $encrypted_mysql_user, string $encrypted_mysql_pass):array {

		$extra = [
			"version" => self::_EXTRA_VERSION,
			"extra"   => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];

		$extra["extra"]["encrypted_mysql_user"] = $encrypted_mysql_user;
		$extra["extra"]["encrypted_mysql_pass"] = $encrypted_mysql_pass;
		return $extra;
	}

	/**
	 * Получаем зашифрованного пользователя для mysql
	 *
	 * @param array $extra
	 *
	 * @return string
	 */
	public static function getEncryptedMysqlUser(array $extra):string {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["encrypted_mysql_user"];
	}

	/**
	 * Получаем зашифрованный пароль для mysql
	 *
	 * @param array $extra
	 *
	 * @return string
	 */
	public static function getEncryptedMysqlPass(array $extra):string {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["encrypted_mysql_pass"];
	}

	/**
	 * Проверяет, может ли порт быть привязан к указанной компании.
	 */
	public static function canBeBoundWithCompany(Struct_Db_PivotCompanyService_PortRegistry $port, int $company_id):bool {

		// если он в статусе void, то он не имеет привязки к компании
		// и связать его можно с любой компаний
		if ($port->status === static::STATUS_VOID) {
			return true;
		}

		// если он в статусе locked, то связать порт можно
		// только с компанией, под которую он был заблокирован
		if ($port->status === self::STATUS_LOCKED && $port->company_id === $company_id) {
			return true;
		}

		return false;
	}

	/**
	 * Проверяет, позволяет ли статус порта привязать к нему компанию.
	 */
	public static function canBeBound(Struct_Db_PivotCompanyService_PortRegistry $port):bool {

		return $port->status !== static::STATUS_ACTIVE && $port->status !== static::STATUS_INVALID;
	}

	/**
	 * Является ли порт сервисным.
	 */
	public static function isService(Struct_Db_PivotCompanyService_PortRegistry $port):bool {

		return $port->type === self::TYPE_SERVICE;
	}

	/**
	 * Получить актуальную структуру для extra
	 *
	 * @param array $extra
	 *
	 * @return array
	 */
	protected static function _getExtra(array $extra):array {

		// если версия не совпадает - дополняем её до текущей
		if ($extra["version"] != self::_EXTRA_VERSION) {

			$extra["extra"]   = array_merge(self::_EXTRA_SCHEMA[self::_EXTRA_VERSION], $extra["extra"]);
			$extra["version"] = self::_EXTRA_VERSION;
		}

		return $extra;
	}
}