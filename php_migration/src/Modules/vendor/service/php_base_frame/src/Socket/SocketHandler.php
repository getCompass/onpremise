<?php

namespace BaseFrame\Socket;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для работы с путями
 */
class SocketHandler {

	private static SocketHandler|null $_instance = null;
	private array                     $_socket_url_config;
	private array                     $_socket_module_config;
	private string                    $_socket_key_me;
	private string                    $_ca_certificate;

	/**
	 * Socket constructor.
	 *
	 * @throws ReturnFatalException
	 */
	private function __construct(array $socket_url_config, array $socket_module_config, string $socket_key_me, string $ca_certificate) {

		$this->_socket_url_config    = $socket_url_config;
		$this->_socket_module_config = $socket_module_config;
		$this->_socket_key_me        = $socket_key_me;
		$this->_ca_certificate       = $ca_certificate;
	}

	/**
	 * инициализируем синглтон
	 *
	 */
	public static function init(array $socket_url_config, array $socket_module_config, string $socket_key_me = "", string $ca_certificate = ""):static {

		if (!is_null(static::$_instance)) {
			return static::$_instance;
		}

		return static::$_instance = new static($socket_url_config, $socket_module_config, $socket_key_me, $ca_certificate);
	}

	/**
	 * Возвращает экземпляр класса.
	 */
	public static function instance():static {

		if (is_null(static::$_instance)) {
			throw new ReturnFatalException("need to initialized before using");
		}

		return static::$_instance;
	}

	/**
	 * получаем socket_url_config
	 *
	 */
	public function configUrl():array {

		return $this->_socket_url_config;
	}

	/**
	 * получаем socket_module_config
	 *
	 */
	public function configModule():array {

		return $this->_socket_module_config;
	}

	/**
	 * получаем socket_key_me
	 *
	 */
	public function keyMe():string {

		return $this->_socket_key_me;
	}

	/**
	 * получаем корневой сертификат
	 *
	 * @return string
	 */
	public function caCertificate():string {

		return $this->_ca_certificate;
	}

}
