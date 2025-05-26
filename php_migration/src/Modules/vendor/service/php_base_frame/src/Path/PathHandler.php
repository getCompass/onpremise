<?php

namespace BaseFrame\Path;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для работы с путями
 */
class PathHandler {

	private static PathHandler|null $_instance = null;
	private string                  $_root_path;
	private string                  $_logs_path;
	private string                  $_config_log_cron_path;
	private string                  $_config_log_exception_path;
	private string                  $_log_error_php_path;
	private string                  $_log_critical_php_exception_path;
	private string                  $_log_error_mysql_path;
	private string                  $_log_admin_path;
	private string                  $_api_path;

	/**
	 * Path constructor.
	 *
	 * @throws ReturnFatalException
	 */
	private function __construct(string $root_path, string $logs_path, string $config_log_cron_path, string $config_log_exception_path, string $log_error_php_path,
					     string $log_critical_php_exception_path, string $log_error_mysql_path, string $log_admin_path, string $api_path) {

		if (mb_strlen($root_path) < 1 || mb_strlen($logs_path) < 1 || mb_strlen($config_log_cron_path) < 1
			|| mb_strlen($config_log_exception_path) < 1 || mb_strlen($log_error_php_path) < 1
			|| mb_strlen($log_critical_php_exception_path) < 1 || mb_strlen($log_error_mysql_path) < 1 || mb_strlen($log_admin_path) < 1) {

			throw new ReturnFatalException("incorrect path");
		}

		$this->_root_path                       = $root_path;
		$this->_logs_path                       = $logs_path;
		$this->_config_log_cron_path            = $config_log_cron_path;
		$this->_config_log_exception_path       = $config_log_exception_path;
		$this->_log_error_php_path              = $log_error_php_path;
		$this->_log_critical_php_exception_path = $log_critical_php_exception_path;
		$this->_log_error_mysql_path            = $log_error_mysql_path;
		$this->_log_admin_path                  = $log_admin_path;
		$this->_api_path                        = $api_path;
	}

	/**
	 * инициализируем синглтон
	 *
	 */
	public static function init(string $root_path, string $logs_path, string $config_log_cron_path, string $config_log_exception_path, string $log_error_php_path,
					    string $log_critical_php_exception_path, string $log_error_mysql_path, string $log_admin_path, string $api_path):static {

		if (!is_null(static::$_instance)) {
			return static::$_instance;
		}

		return static::$_instance = new static(
			$root_path, $logs_path, $config_log_cron_path, $config_log_exception_path, $log_error_php_path,
			$log_critical_php_exception_path, $log_error_mysql_path, $log_admin_path, $api_path
		);
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
	 * получаем root_path
	 *
	 */
	public function root():string {

		return $this->_root_path;
	}

	/**
	 * получаем logs_path
	 *
	 */
	public function logs():string {

		return $this->_logs_path;
	}

	/**
	 * получаем config_log_cron_path
	 *
	 */
	public function configLogCron():string {

		return $this->_config_log_cron_path;
	}

	/**
	 * получаем config_log_exception_path
	 *
	 */
	public function configLogException():string {

		return $this->_config_log_exception_path;
	}

	/**
	 * получаем log_error_php_path
	 *
	 */
	public function logErrorPhp():string {

		return $this->_log_error_php_path;
	}

	/**
	 * получаем log_critical_php_exception_path
	 *
	 */
	public function logCriticalPhpException():string {

		return $this->_log_critical_php_exception_path;
	}

	/**
	 * получаем log_error_mysql_path
	 *
	 */
	public function logErrorMysql():string {

		return $this->_log_error_mysql_path;
	}

	/**
	 * получаем log_admin_path
	 *
	 */
	public function logAdmin():string {

		return $this->_log_admin_path;
	}

	/**
	 * получаем api_path
	 *
	 */
	public function api():string {

		return $this->_api_path;
	}
}
