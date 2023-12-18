<?php

namespace Compass\Thread;

/**
 * Класс, от которого нужно наследовать любые скрипты обновления компании.
 */
abstract class Type_Script_CompanyUpdateTemplate {

	/** @var int последнее время фиксации */
	protected int $_last_timestamp = 0;

	/** @var string готовая строка для времени */
	protected string $_time = "";

	/** @var array данные об исполнении */
	protected array $_log = [];

	/** @var array лог ошибок */
	protected array $_error_log = [];

	/**
	 * Type_Script_CompanyUpdateTemplate constructor.
	 *
	 * @param int $_mask
	 */
	public function __construct(protected int $_mask) {

		// ctor
	}

	/**
	 * Точка входа в скрипт.
	 *
	 * @param array $data
	 */
	abstract public function exec(array $data):void;

	/**
	 * Возвращает лог исполнения.
	 *
	 * @return string
	 */
	#[\JetBrains\PhpStorm\Pure]
	public function getLog():string {

		return implode("\n", $this->_log);
	}

	/**
	 * Возвращает лог ошибок.
	 *
	 * @return string
	 */
	#[\JetBrains\PhpStorm\Pure]
	public function getError():string {

		return implode("\n", $this->_error_log);
	}

	/**
	 * Логирование данных при исполнении.
	 * Формирует массив, который затем можно будет вернуть на пивот.
	 *
	 * @param string $message
	 */
	protected function _log(string $message):void {

		$this->_log[] = $this->_makeRow($message);
	}

	/**
	 * Логирование данных при исполнении.
	 * Формирует массив, который затем можно будет вернуть на пивот.
	 *
	 * @param string $message
	 */
	protected function _error(string $message):void {

		$this->_log($message);
		$this->_error_log[] = $this->_makeRow($message);
	}

	/**
	 * Проверяет, является ли вызов скрипта асинхронным.
	 *
	 * @return bool
	 */
	#[\JetBrains\PhpStorm\Pure]
	protected function _isDry():bool {

		return Type_Script_Handler::isDry($this->_mask);
	}

	/**
	 * @param callable $fn
	 *
	 * @return mixed
	 */
	protected function _onDry(callable $fn):void {

		if ($this->_isDry()) {
			$fn();
		}
	}

	/**
	 * @param callable $fn
	 *
	 * @return mixed
	 */
	protected function _onNonDry(callable $fn):void {

		if (!$this->_isDry()) {
			$fn();
		}
	}

	/**
	 * Формирует строку с датой..
	 *
	 * @param string $message
	 *
	 * @return string
	 */
	protected function _makeRow(string $message):string {

		return $this->_getTime() . " " . $message;
	}

	/**
	 * Возвращает время для подстановки в строку логирования.
	 *
	 * @return string
	 */
	protected function _getTime():string {

		$time = time();

		if ($time !== $this->_last_timestamp) {
			$this->_time = date("[m/d/y H:i:s]", $time);
		}

		return $this->_time;
	}
}