<?php

namespace BaseFrame\Http\Authorization;

/**
 * Данные, которые будут переданы клиенту для авторизации запросов в дальнейшей работе.
 * Текущая реализация работает через singleton из-за необходимости писать/получать данные в разных кусках кода
 */
class Data {

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

		return isset($this->_data["action"]);
	}

	/**
	 * Устанавливает значение, которые должен будет сохранить клиент.
	 */
	public function set(string $key, string $value):void {

		$this->_data = [
			"action" => static::ACTION_SET,
			"data"   => ["unique" => $key, "token" => $value]
		];
	}

	/**
	 * Устанавливает значение, которые клиент должен удалить у себя.
	 */
	public function drop(string $key):void {

		$this->_data = [
			"action" => static::ACTION_DROP,
			"data"   => ["unique" => $key]
		];
	}

	/**
	 * Возвращает данные авторизации для клиента.
	 */
	public function get():array {

		if (!$this->hasChanges()) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("no auth data set during request");
		}

		return $this->_data;
	}
}