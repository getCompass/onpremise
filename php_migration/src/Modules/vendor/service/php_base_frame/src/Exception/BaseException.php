<?php

namespace BaseFrame\Exception;

use Exception;

/**
 * Основной класс исключений в приложении
 */
class BaseException extends Exception {

	// константы класса
	const IS_CRITICAL = false;
	const HTTP_CODE   = 500;

	// переменная для модуля, где произошла ошибка
	protected string|false $_module;

	// переменная, чтобы понять, критическая ли ошибка
	protected bool $_is_critical;

	// http код ответа
	protected int $_http_code;

	public function __construct(string $message) {

		// проверяем, что исключение можно выбросить
		$this->checkIsAllowedToThrow();

		parent::__construct($message);

		// записываем http_code выпавшего исключения
		$this->_http_code = static::HTTP_CODE;

		// узнаем, в каком модуле произошла ошибка
		$module_string = explode("src/Compass/", ($this->getFile()));

		// записываем, критическая ли ошибка упала
		$this->_is_critical = static::IS_CRITICAL;

		// проверяем, что это действительно сабмодуль и получаем его название
		if (count($module_string) > 1) {
			$this->_module = explode("/", $module_string[1])[0];
			return;
		}

		// если ошибка не в модуле, то значит module = false
		$this->_module = false;
	}

	/**
	 * Вернуть модуль, где произошла ошибка
	 *
	 * @return string|false
	 */
	public function getModule():string|false {

		return $this->_module;
	}

	/**
	 * Вернуть http code
	 *
	 * @return int
	 */
	public function getHttpCode():int {

		return $this->_http_code;
	}

	/**
	 * Вернуть значение флага, является ли ошибка критической
	 *
	 * @return bool
	 */
	public function getIsCritical():bool {

		return $this->_is_critical;
	}

	/**
	 * Можно ли выкинуть исключение
	 *
	 * @return void
	 */
	public function checkIsAllowedToThrow():void {

		if ($this::class == BaseException::class || parent::class == BaseException::class) {
			throw new \Error("Tried to throw main exception " . $this::class);
		}
	}
}