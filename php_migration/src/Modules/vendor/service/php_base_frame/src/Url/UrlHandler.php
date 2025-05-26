<?php

namespace BaseFrame\Url;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для работы с url
 */
class UrlHandler {

	private static UrlHandler|null $_instance = null;
	private string                 $_pivot_domain;

	/**
	 * Url constructor.
	 *
	 * @throws ReturnFatalException
	 */
	private function __construct(string $pivot_domain) {

		if (mb_strlen($pivot_domain) < 1) {
			throw new ReturnFatalException("incorrect domain");
		}

		$this->_pivot_domain = $pivot_domain;
	}

	/**
	 * инициализируем синглтон
	 *
	 */
	public static function init(string $pivot_domain):static {

		if (!is_null(static::$_instance)) {
			return static::$_instance;
		}

		return static::$_instance = new static($pivot_domain);
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
	 * получаем pivot_domain
	 *
	 */
	public function pivotDomain():string {

		return $this->_pivot_domain;
	}
}
