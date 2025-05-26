<?php declare(strict_types=1);

namespace BaseFrame\Database;

/**
 * Класс, отвечающий за генерацию экземпляров PDO-подключений.
 */
class Provider {

	/** @var ?self экземпляр для singleton */
	protected static self|null $_instance = null;

	/** @var \BaseFrame\Database\PDODriver[] хранилище ранее инициализированных подключений */
	protected array $_store = [];

	/**
	 * Поставщик подключений для PDO.
	 * Закрываем конструктор.
	 */
	protected function __construct() {

	}

	/**
	 * Возвращает экземпляр класса для работы.
	 */
	public static function instance():static {

		if (is_null(static::$_instance)) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}

	/**
	 * Возвращает экземпляр подключения для указанного конфигурационного файла.
	 */
	public function connect(Config\Connection $conn_config, Config\Query $query_config, string $key = ""):PDODriver {

		if ($key === "") {
			$key = $conn_config->getDSN();
		}

		if (!isset($this->_store[$key])) {
			$this->_store[$key] = PDODriver::instance($conn_config, $query_config);
		}

		return $this->_store[$key];
	}
}