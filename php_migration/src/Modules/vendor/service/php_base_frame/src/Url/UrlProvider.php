<?php

namespace BaseFrame\Url;

/**
 * Класс-обертка для работы с url
 */
class UrlProvider {

	/**
	 * Закрываем конструктор.
	 */
	protected function __construct() {

	}

	/**
	 * получаем pivot_domain
	 *
	 */
	public static function pivotDomain():string {

		return UrlHandler::instance()->pivotDomain();
	}
}
