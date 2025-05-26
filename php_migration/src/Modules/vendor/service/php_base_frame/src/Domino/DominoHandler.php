<?php

namespace BaseFrame\Domino;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для работы с путями
 */
class DominoHandler {

	private static DominoHandler|null $_instance = null;
	private string                    $_domino_id;

	/**
	 * Domino constructor.
	 *
	 * @throws ReturnFatalException
	 */
	private function __construct(string $domino_id) {

		if (mb_strlen($domino_id) < 1) {
			throw new ReturnFatalException("incorrect domino");
		}

		$this->_domino_id = $domino_id;
	}

	/**
	 * инициализируем синглтон
	 *
	 */
	public static function init(string $domino_id):static {

		if (!is_null(static::$_instance)) {
			return static::$_instance;
		}

		return static::$_instance = new static($domino_id);
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
	 * получаем domino_id
	 *
	 */
	public function id():string {

		return $this->_domino_id;
	}
}
