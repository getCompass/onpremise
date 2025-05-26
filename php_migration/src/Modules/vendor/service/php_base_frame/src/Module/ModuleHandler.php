<?php

namespace BaseFrame\Module;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для работы с модулями
 */
class ModuleHandler {

	private static ModuleHandler|null $_instance = null;
	private string                    $_current_module;

	/**
	 * Module constructor.
	 *
	 * @throws ReturnFatalException
	 */
	private function __construct(string $current_module) {

		if (mb_strlen($current_module) < 1) {
			throw new ReturnFatalException("incorrect current_module");
		}

		$this->_current_module = $current_module;
	}

	/**
	 * инициализируем синглтон
	 *
	 */
	public static function init(string $current_module):static {

		if (!is_null(static::$_instance)) {
			return static::$_instance;
		}

		return static::$_instance = new static($current_module);
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
	 * получаем root_path
	 *
	 */
	public function current():string {

		return $this->_current_module;
	}
}
