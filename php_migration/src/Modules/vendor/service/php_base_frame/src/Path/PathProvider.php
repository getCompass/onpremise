<?php

namespace BaseFrame\Path;

/**
 * Класс-обертка для работы с путями
 */
class PathProvider {

	/**
	 * Закрываем конструктор.
	 */
	protected function __construct() {

	}

	/**
	 * получаем root_path
	 *
	 */
	public static function root():string {

		return PathHandler::instance()->root();
	}

	/**
	 * получаем logs_path
	 *
	 */
	public static function logs():string {

		return PathHandler::instance()->logs();
	}

	/**
	 * получаем config_log_cron_path
	 *
	 */
	public static function configLogCron():string {

		return PathHandler::instance()->configLogCron();
	}

	/**
	 * получаем config_log_exception_path
	 *
	 */
	public static function configLogException():string {

		return PathHandler::instance()->configLogException();
	}

	/**
	 * получаем log_error_php_path
	 *
	 */
	public static function logErrorPhp():string {

		return PathHandler::instance()->logErrorPhp();
	}

	/**
	 * получаем log_critical_php_exception_path
	 *
	 */
	public static function logCriticalPhpException():string {

		return PathHandler::instance()->logCriticalPhpException();
	}

	/**
	 * получаем log_error_mysql_path
	 *
	 */
	public static function logErrorMysql():string {

		return PathHandler::instance()->logErrorMysql();
	}

	/**
	 * получаем log_admin_path
	 *
	 */
	public static function logAdmin():string {

		return PathHandler::instance()->logAdmin();
	}

	/**
	 * получаем api_path
	 *
	 */
	public static function api():string {

		return PathHandler::instance()->api();
	}
}
