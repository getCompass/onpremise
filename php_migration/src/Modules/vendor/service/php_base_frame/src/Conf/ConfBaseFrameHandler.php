<?php

namespace BaseFrame\Conf;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для работы с конфигами base_frame
 */
class ConfBaseFrameHandler {

	public const CONFIG_MAX_EXECUTION_TIME = 10;
	public const CONFIG_WEB_CHARSET        = "UTF-8";
	public const CONFIG_SQL_CHARSET        = "utf8mb4";

	// дефолтный список header
	public const    _DEFAULT_HEADER_LAST_MODIFIED = "Last-Modified: ";
	protected const _DEFAULT_HEADER_LIST          = [
		"Cache-Control: no-store, no-cache, must-revalidate",
		"Pragma: no-cache",
		"Content-type: text/html;charset=" . self::CONFIG_WEB_CHARSET,
	];

	private static ConfBaseFrameHandler|null $_instance = null;
	private int                              $_config_max_execution_time;
	private string                           $_config_web_charset;
	private string                           $_config_sql_charset;

	/**
	 * Conf constructor.
	 *
	 * @throws ReturnFatalException
	 */
	private function __construct(int $config_max_execution_time, string $config_web_charset, string $config_sql_charset) {

		$this->_config_max_execution_time = $config_max_execution_time;
		$this->_config_web_charset        = $config_web_charset;
		$this->_config_sql_charset        = $config_sql_charset;
	}

	/**
	 * инициализируем синглтон
	 *
	 */
	public static function init(int $config_max_execution_time, string $config_web_charset, string $config_sql_charset):static {

		if ($config_max_execution_time < 1 || mb_strlen($config_web_charset) < 1 || mb_strlen($config_sql_charset) < 1) {
			throw new ReturnFatalException("incorrect conf param");
		}

		if (!is_null(static::$_instance)) {
			return static::$_instance;
		}

		return static::$_instance = new static($config_max_execution_time, $config_web_charset, $config_sql_charset);
	}

	/**
	 * Возвращает экземпляр класса.
	 */
	public static function instance():static {

		if (is_null(static::$_instance)) {
			return static::$_instance = new static(self::CONFIG_MAX_EXECUTION_TIME, self::CONFIG_WEB_CHARSET, self::CONFIG_SQL_CHARSET);
		}

		return static::$_instance;
	}

	/**
	 * устанавливаем header
	 */
	public function setHeaderList(array $header_list):void {

		if (headers_sent()) {
			return;
		}

		$header_list[] = self::_DEFAULT_HEADER_LAST_MODIFIED . gmdate("D, d M Y H:i:s") . " GMT";

		// мержим конечные значение, заменяя дефолтные значения переданными
		$finally_header_list = array_merge(self::_DEFAULT_HEADER_LIST, $header_list);

		// устанавливаем
		foreach ($finally_header_list as $value) {
			header($value);
		}
	}

	/**
	 * получаем max_execution_time
	 *
	 */
	public function maxExecutionTime():int {

		return $this->_config_max_execution_time;
	}

	/**
	 * получаем web_charset
	 *
	 */
	public function webCharset():string {

		return $this->_config_web_charset;
	}

	/**
	 * получаем sql_charset
	 *
	 */
	public function sqlCharset():string {

		return $this->_config_sql_charset;
	}

}
