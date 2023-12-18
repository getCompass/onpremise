<?php

namespace Compass\Speaker;

// Main
require_once __DIR__ . "/../../../../start.php";

// проверяем, что заголовок запроса имеет content-type application/json
if ($_SERVER["CONTENT_TYPE"] != "application/json") {
	throw new \paramException("Janus returned not JSON");
}

// получаем информацию о ноде по адресу обращения
$requester_host = getIp();
try {

	if (isTestServer()) {
		$janus_item = Type_Call_Config::getJanusByHost(PIVOT_DOMAIN, $requester_host);
	} else {
		$janus_item = Type_Call_Config::getJanusByHost(getDomain(), $requester_host);
	}
} catch (cs_Janus_Node_Not_Exist) {

	throw new \userAccessException("access denied");
}

// получаем данные запроса
$request_data = file_get_contents("php://input");

// в зависимости от включенного параметра is_grouping обрабатываем по разному
if ($janus_item["is_grouping"] == 1) {

	$event_list = fromJson($request_data);

	// если включена константа на сортировку группы событий по приоритетности
	if (SORT_EVENTS_BY_IMPORTANCE_IS_ENABLED) {
		$event_list = Type_Janus_Event_Handler::doSortByImportance($event_list);
	}

	// пробегаемся по ним
	foreach ($event_list as $event_item) {
		Type_Janus_Event_Handler::doHandle($event_item);
	}
} elseif ($janus_item["is_grouping"] == 0) {

	// получаем событие
	$event_item = fromJson($request_data);
	Type_Janus_Event_Handler::doHandle($event_item);
}

showAjax([
	"status"   => "ok",
	"response" => [],
]);