<?php

namespace Compass\Company;

/**
 * Генератор проверки не нужно ли запустить задачи по периодической рассылке данных для компаний.
 */
class Type_Generator_Company_Sender extends Type_Generator_Abstract {

	public const GENERATOR_TYPE = "company_sender";

	/**
	 * Получить параметры генератора событий.
	 */
	public static function getOptions():array {

		$generator_options = parent::getOptions();

		$generator_options["subscription_item"]["event"] = Type_Event_Company_SenderCheckRequired::EVENT_TYPE;

		return $generator_options;
	}
}