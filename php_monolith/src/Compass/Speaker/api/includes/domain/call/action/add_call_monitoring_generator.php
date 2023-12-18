<?php

declare(strict_types = 1);

namespace Compass\Speaker;

/**
 * Action для создания генератора событий в go_event
 */
class Domain_Call_Action_AddCallMonitoringGenerator {

	protected const _GENERATOR_NAME = "monitoring_observer";

	/**
	 * выполняем
	 *
	 * @throws \parseException
	 */
	public static function do():void {

		// получаем данные для генератора
		$generator_data = getConfig("GENERATOR")[self::_GENERATOR_NAME];

		$subscription_item = Struct_Event_System_SubscriptionItem::build(...$generator_data["subscription_item"]);

		// добавляем генератор для мониторинга звонков
		Domain_System_Action_Event_AddGenerator::do(
			self::_GENERATOR_NAME, $generator_data["period"], $subscription_item, $generator_data["event_data"]
		);
	}
}