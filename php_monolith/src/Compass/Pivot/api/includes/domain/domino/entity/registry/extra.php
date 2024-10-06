<?php

namespace Compass\Pivot;

use JetBrains\PhpStorm\ArrayShape;

/**
 * сущность экстры для реестра доминошек
 */
class Domain_Domino_Entity_Registry_Extra {

	protected const _EXTRA_VERSION = 2; // версия упаковщика
	protected const _EXTRA_SCHEMA  = [  // схема extra

		1 => [
			"go_database_controller_port" => 0,
			"url"                         => "",
		],
		2 => [
			"go_database_controller_port" => 0,
			"url"                         => "",
			"go_database_controller_host" => "",
		],
	];

	/**
	 * Создать новую структуру для extra
	 *
	 * @param string $go_database_controller_host
	 * @param int    $go_database_controller_port
	 * @param string $url
	 *
	 * @return array
	 */
	#[ArrayShape(["version" => "int", "extra" => "int[]"])]
	public static function initExtra(string $go_database_controller_host, int $go_database_controller_port, string $url):array {

		$extra = [
			"version" => self::_EXTRA_VERSION,
			"extra"   => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];

		$extra["extra"]["go_database_controller_port"] = $go_database_controller_port;
		$extra["extra"]["go_database_controller_host"] = $go_database_controller_host;
		$extra["extra"]["url"]                         = $url;

		return $extra;
	}

	/**
	 * Получаем порт для контроллера базы данных
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getGoDatabaseControllerPort(array $extra):int {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["go_database_controller_port"];
	}

	/**
	 * Получаем хост для контроллера базы данных
	 *
	 * @param array $extra
	 *
	 * @return string
	 */
	public static function getGoDatabaseControllerHost(array $extra):string {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["go_database_controller_host"];
	}

	/**
	 * Получаем url
	 *
	 * @param array $extra
	 *
	 * @return string
	 */
	public static function getUrl(array $extra):string {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["url"];
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