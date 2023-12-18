<?php

namespace Compass\Company;

/**
 * Генератор события проверки последней активности компании.
 */
class Type_Generator_Company_CheckLastActivity extends Type_Generator_Abstract {

	public const GENERATOR_TYPE = "check_last_activity";

	/**
	 * Требуется ли пропустить добавление генератора.
	 */
	public static function isSkipAdding():bool {

		return false === \CompassApp\Company\HibernationHandler::instance()->isNeedHibernation();
	}

	/**
	 * Получить параметры генератора событий.
	 */
	public static function getOptions():array {

		$generator_options = parent::getOptions();

		$generator_options["period"]                     = 300 + (COMPANY_ID % 100);
		$generator_options["subscription_item"]["event"] = Type_Event_Company_CheckLastActivity::EVENT_TYPE;

		return $generator_options;
	}
}