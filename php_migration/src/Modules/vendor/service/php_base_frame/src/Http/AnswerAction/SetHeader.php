<?php

namespace BaseFrame\Http\AnswerAction;

/**
 * Класс-обертка для возврата клиенту необходимости установить заголовок.
 */
class SetHeader {

	public const ACTION_SET  = "set";
	public const ACTION_DROP = "drop";

	protected static self|null $_instance = null;

	/** @var array данные с изменениями */
	protected array $_data = [];

	/**
	 * Закрываем конструктор, работаем через singleton.
	 */
	protected function __construct() {

	}

	/**
	 * Возвращает экземпляр объекта клиентских данных авторизации.
	 * @see \BaseFrame\Http\Authorization\Data
	 */
	public static function inst():static {

		if (is_null(static::$_instance)) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}

	/**
	 * Проверяет наличие изменений, если изменений
	 * нет, то клиенту ничего передавать не нужно.
	 */
	public function hasChanges():bool {

		return count($this->_data) > 0;
	}

	/**
	 * Готовит данные для action с установкой заголовка.
	 */
	public function set(string $name, string $key, string $value):void {

		$this->_data[$name] = ["action" => static::ACTION_SET, "data" => [
			"name"   => $name,
			"unique" => $key,
			"value"  => $value
		]];
	}

	/**
	 * Готовит данные для action со сбросом заголовка.
	 */
	public function drop(string $name, string $key):void {

		$this->_data[$name] = ["action" => static::ACTION_DROP, "data" => [
			"name"   => $name,
			"unique" => $key,
		]];
	}

	/**
	 * Возвращает данные авторизации для клиента.
	 */
	public function get():array {

		if (!$this->hasChanges()) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("no header data set during request");
		}

		return array_values($this->_data);
	}
}