<?php

namespace Compass\Federation;

/**
 * класс для работы с конфиг-файлом LDAP
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_Config {

	/** ключ под которым хранится конфигурация */
	protected const _KEY = "LDAP";

	/**
	 * получаем host сервера из конфига LDAP
	 *
	 * @return string
	 */
	public static function getServerHost():string {

		$config = getConfig(self::_KEY);
		return $config["host"];
	}

	/**
	 * получаем port сервера из конфига LDAP
	 *
	 * @return int
	 */
	public static function getServerPort():int {

		$config = getConfig(self::_KEY);
		return $config["port"];
	}

	/**
	 * получаем user_search_base из конфига LDAP
	 *
	 * @return string
	 */
	public static function getUserSearchBase():string {

		$config = getConfig(self::_KEY);
		return $config["user_search_base"];
	}

	/**
	 * получаем user_search_page_size из конфига LDAP
	 *
	 * @return int
	 */
	public static function getUserSearchPageSize():int {

		$config = getConfig(self::_KEY);
		return $config["user_search_page_size"];
	}

	/**
	 * получаем user_unique_attribute из конфига LDAP
	 *
	 * @return string
	 */
	public static function getUserUniqueAttribute():string {

		$config = getConfig(self::_KEY);
		return $config["user_unique_attribute"];
	}

	/**
	 * включен ли мониторинг удаления / блокировки учетной записи LDAP для запуска автоматической блокировки связанного пользователя в Compass
	 *
	 * @return bool
	 */
	public static function isAccountDisablingMonitoringEnabled():bool {

		$config = getConfig(self::_KEY);
		return $config["account_disabling_monitoring_enabled"];
	}

	/**
	 * получаем monitoring_user_dn из конфига LDAP
	 *
	 * @return string
	 */
	public static function getAccountDisablingMonitoringUserDn():string {

		$config = getConfig(self::_KEY);
		return $config["account_disabling_monitoring_dn"];
	}

	/**
	 * получаем monitoring_user_password из конфига LDAP
	 *
	 * @return string
	 */
	public static function getAccountDisablingMonitoringUserPassword():string {

		$config = getConfig(self::_KEY);
		return $config["account_disabling_monitoring_password"];
	}

	/**
	 * получаем уровень жесткости блокировки Compass пользователя при удалении связанной
	 * LDAP учетной записи из каталога
	 *
	 * @return Domain_Ldap_Entity_UserBlocker_Level
	 */
	public static function getUserBlockingLevelOnAccountRemoving():Domain_Ldap_Entity_UserBlocker_Level {

		$config = getConfig(self::_KEY);
		return Domain_Ldap_Entity_UserBlocker_Level::from($config["on_account_removing"]);
	}

	/**
	 * получаем уровень жесткости блокировки Compass пользователя при отключении связанной
	 * LDAP учетной записи из каталога
	 *
	 * @return Domain_Ldap_Entity_UserBlocker_Level
	 */
	public static function getUserBlockingLevelOnAccountDisabling():Domain_Ldap_Entity_UserBlocker_Level {

		$config = getConfig(self::_KEY);
		return Domain_Ldap_Entity_UserBlocker_Level::from($config["on_account_disabling"]);
	}

	/**
	 * временной интервал между проверками мониторинга блокировки пользователя LDAP
	 *
	 * @return string
	 */
	public static function getAccountDisablingMonitoringInterval():string {

		$config = getConfig(self::_KEY);
		return $config["account_disabling_monitoring_interval"];
	}

	/**
	 * лимит неудачных попыток аутентификации, по достижению которых ip адрес пользователя получает блокировку на 15 минут
	 *
	 * @return int
	 */
	public static function getLimitOfIncorrectAuthAttempts():int {

		$config = getConfig(self::_KEY);
		return $config["limit_of_incorrect_auth_attempts"];
	}
}