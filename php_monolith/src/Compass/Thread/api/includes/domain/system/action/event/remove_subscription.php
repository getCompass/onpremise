<?php

namespace Compass\Thread;

/**
 * Action для создания события на удаление подписчика
 */
class Domain_System_Action_Event_RemoveSubscription {

	/**
	 *
	 * @throws \parseException
	 */
	public static function do(int $address_type, string $address, string $address_method, string $event):void {

		$subscription = Struct_Event_System_SubscriptionItem::build($address_type, $event, [
			"module" => $address,
			"method" => $address_method,
		]);

		$event_data = Type_Event_System_SubscriptionRemoved::create("php_" . CURRENT_MODULE, $subscription);
		Gateway_Event_Dispatcher::dispatchService($event_data);
	}
}