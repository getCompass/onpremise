<?php

namespace Compass\Federation;

/**
 * класс для логирования проишествий с попытками SSO аутентификацией
 * @package Compass\Federation
 */
class Domain_Oidc_Entity_Logger {

	/** название файла, куда логируем */
	protected const _FILE_NAME = "sso_oidc";

	/**
	 * логируем
	 */
	public static function log(string $message, array $extra_data = []):void {

		Type_System_Admin::log(self::_FILE_NAME, ["message" => $message, "extra" => $extra_data]);
	}
}