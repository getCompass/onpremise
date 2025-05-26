<?php

namespace BaseFrame\Database;

/**
 * Класс-хук для выполнения преобразований перед записью/чтением из БД.
 */
class Hook {

	public function __construct(
		protected string      $_db_name,
		protected string      $_table_name,
		protected string      $_column_name,
		protected Hook\Action $_action,
		protected ?\Closure   $_fn,
		protected ?\Closure   $_recover_fn = null,
	) {

	}

	/**
	 * Должен ли хук выполняться при чтении.
	 */
	public function getAction():string {

		return $this->_action->value;
	}

	/**
	 * Выполняет функцию хук.
	 */
	public function exec(mixed $value):mixed {

		return $this->_fn->call($this, $value);
	}

	/**
	 * Выполняет функцию восстановления.
	 * @throws
	 */
	public function recover(mixed $value, \Throwable $e):mixed {

		if (is_null($this->_recover_fn)) {
			throw $e;
		}

		 return $this->_recover_fn->call($this, $value, $e);
	}

	/**
	 * Возвращает имя базы данных для хука.
	 */
	public function getDb():string {

		return $this->_db_name;
	}

	/**
	 * Возвращает имя таблицы для хука.
	 */
	public function getTable():string {

		return $this->_table_name;
	}

	/**
	 * Возвращает название колонки для хука.
	 */
	public function getColumn():string {

		return $this->_column_name;
	}

	protected static function _recover():void {

		throw new \RuntimeException();
	}
}
