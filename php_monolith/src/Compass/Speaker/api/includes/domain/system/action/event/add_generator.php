<?php

namespace Compass\Speaker;

/**
 * Action для создания генератора событий в go_event
 */
class Domain_System_Action_Event_AddGenerator {

	/**
	 * @param string                               $generator_name    имя генератора
	 * @param int                                  $period            частота генерации событий (в секундах)
	 * @param Struct_Event_System_SubscriptionItem $subscription_item подписчик
	 * @param array                                $event_data        дополнительные данные
	 *
	 * @throws \parseException
	 */
	public static function do(string $generator_name, int $period, Struct_Event_System_SubscriptionItem $subscription_item, array $event_data = []):void {

		$event = Type_Event_System_GeneratorAdded::create($generator_name, $period, $subscription_item, $event_data);

		Gateway_Event_Dispatcher::dispatchService($event);
	}
}