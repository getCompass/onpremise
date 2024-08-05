<?php

namespace Compass\Federation;

/**
 * Генератор запуска механизма автоматической блокировки пользователя Compass, если его связанный аккаунт в LDAP
 * был удален или заблокирован
 */
class Type_Generator_Ldap_AccountChecker extends Type_Generator_Abstract {

	public const GENERATOR_TYPE = "ldap_account_checker";

	/**
	 * Получить параметры генератора событий.
	 */
	public static function getOptions():array {

		$generator_options = parent::getOptions();

		// выполним задачу снова через интервал указанный в конфиге
		$interval                                        = Domain_Ldap_Entity_Config::getAccountDisablingMonitoringInterval();
		$generator_options["only_for_global"]            = true;
		$generator_options["period"]                     = Domain_Ldap_Entity_Utils::convertIntervalToSec($interval);
		$generator_options["subscription_item"]["event"] = Type_Event_Ldap_AccountChecker::EVENT_TYPE;

		return $generator_options;
	}
}