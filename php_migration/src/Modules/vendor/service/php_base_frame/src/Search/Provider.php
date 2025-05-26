<?php declare(strict_types=1);

namespace BaseFrame\Search;

/**
 * Класс, отвечающий за генерацию экземпляров подключений к Manticore Search.
 */
class Provider {

	// экземпляр для singleton
	protected static self|null $_instance = null;

	/** @var Manticore[] хранилище ранее инициализированных подключений */
	protected array $_store = [];

	/**
	 * Поставщик подключений для Manticore Search.
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
	public function connect(Config\Connection $sharding_config, string $key = ""):Manticore {

		if ($key === "") {
			$key = "$sharding_config->host:$sharding_config->port";
		}

		if (!isset($this->_store[$key])) {
			$this->_store[$key] = Manticore::instance($sharding_config);
		}

		return $this->_store[$key];
	}
}