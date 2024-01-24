<?php

namespace Compass\Userbot;

use JetBrains\PhpStorm\Pure;

/**
 * класс для работы с конфиг-файлом (api/conf/url.php) проекта
 */
class Type_System_Url {

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// возвращает URL
	public static function getPivotUrl():string {

		return PUBLIC_ENTRYPOINT_PIVOT;
	}

	// возвращает domain
	#[Pure] public static function getDomain():string {

		$url = self::getPivotUrl();

		// получаем domain
		return parse_url($url, PHP_URL_HOST);
	}

	// возвращает protocol
	#[Pure] public static function getProtocol():string {

		$url = self::getPivotUrl();

		// получаем protocol
		return parse_url($url, PHP_URL_SCHEME);
	}

	// функция для получения url start
	public static function getStartUrl():string {

		$conf_global = getConfig("URL_GLOBAL");

		return $conf_global["start_url"];
	}
}