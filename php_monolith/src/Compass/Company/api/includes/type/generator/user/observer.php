<?php

namespace Compass\Company;

/**
 * Генератор проверки не нужно ли запустить задачи наблюдателя пользователей.
 */
class Type_Generator_User_Observer extends Type_Generator_Abstract {

	public const GENERATOR_TYPE = "user_observer";

	/**
	 * Получить параметры генератора событий.
	 */
	public static function getOptions():array {

		$generator_options = parent::getOptions();

		$generator_options["subscription_item"]["event"] = Type_Event_User_ObserverCheckRequired::EVENT_TYPE;

		return $generator_options;
	}
}