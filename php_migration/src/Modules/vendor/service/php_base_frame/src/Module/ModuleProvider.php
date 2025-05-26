<?php

namespace BaseFrame\Module;

/**
 * Класс-обертка для работы с модулями
 */
class ModuleProvider {

	/**
	 * Закрываем конструктор.
	 */
	protected function __construct() {

	}

	/**
	 * получаем current_module
	 *
	 */
	public static function current():string {

		return ModuleHandler::instance()->current();
	}
}
