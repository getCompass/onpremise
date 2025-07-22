<?php

namespace Compass\Federation;

/**
 * Генератор запуска механизма автоматической актуализации данных пользователя в компасе после их изменений в ldap
 */
class Type_Generator_Ldap_ProfileUpdater extends Type_Generator_Abstract {

	public const GENERATOR_TYPE = "ldap_profile_updater";

	/**
	 * Получить параметры генератора событий.
	 */
	public static function getOptions():array {

		$generator_options = parent::getOptions();

		// выполним задачу снова через интервал указанный в конфиге
		$interval                                        = Domain_Ldap_Entity_Config::getProfileUpdateInterval();
		$generator_options["only_for_global"]            = true;
		$generator_options["period"]                     = Domain_Ldap_Entity_Utils::convertIntervalToSec($interval);
		$generator_options["subscription_item"]["event"] = Type_Event_Ldap_ProfileUpdater::EVENT_TYPE;

		return $generator_options;
	}
}