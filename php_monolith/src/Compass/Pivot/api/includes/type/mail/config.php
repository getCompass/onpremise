<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с конфигом api/conf/mail.php
 * @package Compass\Pivot
 */
class Type_Mail_Config {

	/** ключ для получения параметров подключения по SMTP */
	protected const _KEY_SMTP = "MAIL_SMTP";

	/**
	 * получаем параметры подключения по smtp
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function getSMTPConnectionParams():array {

		$config = self::_getConfig(self::_KEY_SMTP);

		return [
			"host"       => (string) $config["host"],
			"port"       => (int) $config["port"],
			"encryption" => (string) $config["encryption"],
			"username"   => (string) $config["username"],
			"password"   => (string) $config["password"],
			"from"       => (string) $config["from"],
		];
	}

	/**
	 * получаем контент конфига
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	protected static function _getConfig(string $config_key):array {

		$config = getConfig($config_key);

		// если пришел пустой конфиг
		if (count($config) < 1) {
			throw new ParseFatalException("unexpected content");
		}

		return $config;
	}
}