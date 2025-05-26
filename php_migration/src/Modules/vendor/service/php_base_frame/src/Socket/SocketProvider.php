<?php

namespace BaseFrame\Socket;

/**
 * Класс-обертка для работы с путями
 */
class SocketProvider {

	/**
	 * Закрываем конструктор.
	 */
	protected function __construct() {

	}

	/**
	 * получаем socket_url_config
	 *
	 */
	public static function configUrl():array {

		return SocketHandler::instance()->configUrl();
	}

	/**
	 * получаем socket_module_config
	 *
	 */
	public static function configModule():array {

		return SocketHandler::instance()->configModule();
	}

	/**
	 * получаем socket_module_config
	 *
	 */
	public static function keyMe():string {

		return SocketHandler::instance()->keyMe();
	}

	/**
	 * Получаем корневой сертификат
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function caCertificate():string {

		return SocketHandler::instance()->caCertificate();
	}
}
