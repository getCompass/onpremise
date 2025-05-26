<?php

namespace BaseFrame\Database\Config;

/**
 * Класс с параметрами конфигурации подключения к MySQL.
 */
class Connection {

	/**
	 * Класс с параметрами конфигурации подключения к MySQL.
	 */
	function __construct(
		public string  $host,
		public ?string $db_name,
		public string  $user,
		public string  $password,
		public string  $ssl,
		public string  $charset = "utf8mb4",
		public array   $options = [],
	) {

		$this->options[\PDO::ATTR_ERRMODE]            ??= \PDO::ERRMODE_EXCEPTION;
		$this->options[\PDO::ATTR_DEFAULT_FETCH_MODE] ??= \PDO::FETCH_ASSOC;
		$this->options[\PDO::ATTR_EMULATE_PREPARES]   ??= true;
	}

	/**
	 * Формирует строку DSN параметры.
	 */
	public function getDSN():string {

		// собираем DSN строку подключения
		$dsn = "mysql:host=$this->host;";

		if (!is_null($this->db_name)) {
			$dsn .= "dbname=$this->db_name;";
		}

		return $dsn . "charset=$this->charset;";
	}
}
