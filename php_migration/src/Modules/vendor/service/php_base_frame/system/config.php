<?php

/**
 * Класс загрузки конфига для модуля.
 * Напрямую вызывать нет смысла нужно использовать функцию {@see \BaseFrame\Database\Config\Connection}.
 */
class Config {
	
	/** @var string путь к корню модуля */
	protected string $_module_path;

	/** @var array известные загруженные конфиги */
	protected array $_loaded = [];

	/**
	 * Config constructor.
	 */
	public function __construct(string $path) {
		
		$this->_module_path = $path;
	}

	/**
	 * Возвращает массив с данными конфига.
	 * @return array
	 */
	public function get(string $config):array {

		$code = strtoupper($config);

		if (isset($this->_loaded[$code])) {
			return $this->_loaded[$code];
		}

		$codes = explode("_", $code);
		$file  = strtolower($codes[0]);

		foreach ($this::_load($file, $this->_module_path) as $config_key => $config_value) {
			$this->_loaded[$config_key] = $config_value;
		};

		if (!isset($this->_loaded[$code])) {
			return [];
		}

		return $this->_loaded[$code];
	}

    /**
     * Установить значение конфига
     *
     * @param string $config
     * @param $data
     * @return void
     */
    public function set(string $config, $data):void {

        $code = strtoupper($config);
        $this->_loaded[$code] = $data;
    }

	/**
	 * Выполнят загрузку файла с конфигом.
	 */
	protected static function _load(string $file, string $path):array {

		$path = $path . "conf/" . $file . ".php";

		if (file_exists($path)) {
			return include($path);
		}

		return [];
	}
}
