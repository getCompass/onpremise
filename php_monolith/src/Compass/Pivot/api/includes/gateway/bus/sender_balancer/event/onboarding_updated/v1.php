<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс описывающий событие event.onboarding.updated версии 1
 */
class Gateway_Bus_SenderBalancer_Event_OnboardingUpdated_V1 extends Gateway_Bus_SenderBalancer_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.onboarding.updated";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"onboarding" => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @param Struct_User_Onboarding $onboarding
	 *
	 * @return Struct_SenderBalancer_Event
	 * @throws ParseFatalException
	 */
	public static function makeEvent(array $onboarding):Struct_SenderBalancer_Event {

		return self::_buildEvent([
			"onboarding" => (object) $onboarding,
		]);
	}
}