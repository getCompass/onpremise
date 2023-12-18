<?php declare(strict_types = 1);

namespace Compass\Thread;

require_once __DIR__ ."/../../../../../start.php";

/**
 * Файл для синхронизации всей информации с go_event
 * синхронизация списка подписок
 * создание генераторов в go_event
 */

// отправляем на go_event все свои подписки
Domain_System_Action_Event_RefreshSubscriptions::do();

// добавляем генераторы в go_event
foreach (getConfig("GENERATOR") as $generator => $data) {

	$subscription_item = Struct_Event_System_SubscriptionItem::build(...$data["subscription_item"]);
	Domain_System_Action_Event_AddGenerator::do($generator, $data["period"], $subscription_item, $data["event_data"]);
}