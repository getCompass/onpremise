<?php

namespace BaseFrame\Domino;

/**
 * Класс-обертка для работы с домино
 */
class DominoProvider {

	/**
	 * Закрываем конструктор.
	 */
	protected function __construct() {

	}

	/**
	 * получаем domino_id
	 *
	 */
	public static function id():string {

		return DominoHandler::instance()->id();
	}
}
