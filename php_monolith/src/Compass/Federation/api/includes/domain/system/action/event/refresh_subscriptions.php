<?php

namespace Compass\Federation;

/**
 * Action для создания события на обновления всех подписок в go_event
 */
class Domain_System_Action_Event_RefreshSubscriptions {

	/**
	 * Выполняет сбор всех подписок.
	 * Создает события подписок и генераторов.
	 *
	 * @return void
	 * @throws \parseException
	 */
	public static function do():void {

		$listener_list     = Type_Event_Handler::instance()->getListenerList();
		$subscription_list = [];

		foreach ($listener_list as $listener_data_list) {

			foreach ($listener_data_list as $listener_data) {

				/** @var Type_Attribute_EventListener $listener */
				$listener = $listener_data["attribute"];

				// составляем список подписок
				$subscription_list[] = $listener->makeSubscriptionItem();
			}
		}

		$event_data = Type_Event_System_SubscriptionUpdated::create("php_" . CURRENT_MODULE, $subscription_list);
		Gateway_Event_Dispatcher::dispatchService($event_data);

		// добавляем генераторы в go_event
		/** @var Type_Generator_Abstract $generator */
		foreach (getConfig("GENERATOR") as $generator) {

			if ($generator::isSkipAdding()) {
				continue;
			}

			$data = $generator::getOptions();

			$subscription_item = Struct_Event_System_SubscriptionItem::build(...$data["subscription_item"]);
			Domain_System_Action_Event_AddGenerator::do($generator::GENERATOR_TYPE, $data["period"], $subscription_item, $data["event_data"]);
		}
	}
}