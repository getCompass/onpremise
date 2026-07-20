<?php

declare(strict_types=1);

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use Curl;

/**
 * Класс конфига прокси сервера
 */
class Domain_Config_Entity_Proxy
{
	// экземпляр синглтона
	private static ?self $_instance = null;

	// протокол прокси сервера
	private string $_protocol;

	// хост прокси сервера
	private string $_host;

	// порт прокси сервера
	private int $_port;

	// пользователь прокси сервера
	private ?string $_username;

	// пароль пользователя прокси сервера
	private ?string $_password;

	/**
	 * Конструктор
	 */
	private function __construct()
	{

		$proxy_config = getConfig("PROXY");

		if ($proxy_config === []) {
			$this->_protocol = "";
			return;
		}

		if ($proxy_config["protocol"] !== "" && !in_array($proxy_config["protocol"], Curl::ALLOWED_PROXY_PROTOCOLS)) {
			throw new ParseFatalException("unknown proxy protocol passed");
		}

		$this->_protocol = trim($proxy_config["protocol"]);
		$this->_host     = trim($proxy_config["host"]);
		$this->_port     = $proxy_config["port"];
		$this->_username = trim($proxy_config["username"]) === "" ? null : $proxy_config["username"];
		$this->_password = trim($proxy_config["password"]) === "" ? null : $proxy_config["password"];
	}

	/**
	 * Получить экземпляр синглтона
	 */
	public static function instance(): self
	{

		// создаем инстанс, если его не существовало
		if (is_null(static::$_instance)) {

			static::$_instance = new static();

			// если на тестовом сервере, ищем в memcache, существует ли переопределенное значение
			if (isTestServer()) {

				/** @var self $instance */
				$instance = ShardingGateway::cache()->get(__CLASS__);

				if ($instance) {
					static::$_instance = $instance;
				}
			}
		}

		return static::$_instance;
	}

	/**
	 * Переопределить конфиг своими значениями
	 */
	public function override(string $protocol, string $host, int $port, ?string $username = null, ?string $password = null): self
	{

		$this->_protocol = $protocol;
		$this->_host     = $host;
		$this->_port     = $port;
		$this->_username = $username;
		$this->_password = $password;

		ShardingGateway::cache()->set(__CLASS__, $this);

		return $this;
	}

	/**
	 * Сбросить текущий инстанс и переопределенные значения
	 */
	public function reset(): self
	{

		ShardingGateway::cache()->delete(__CLASS__);
		static::$_instance = new static();

		return static::$_instance;
	}

	/**
	 * Получить протокол
	 */
	public function getProtocol(): string
	{

		return $this->_protocol;
	}

	/**
	 * Получить хост
	 */
	public function getHost(): string
	{
		return $this->_host;
	}

	/**
	 * Получить порт
	 */
	public function getPort(): int
	{
		return $this->_port;
	}

	/**
	 * Получить пользователя
	 */
	public function getUsername(): ?string
	{
		return $this->_username;
	}

	/**
	 * Получить пароль
	 */
	public function getPassword(): ?string
	{
		return $this->_password;
	}
}
