<?php

namespace Compass\Speaker;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Action для получения и сохранения аналитики по звонкам
 */
class Domain_Call_Action_GetAndSaveAnalyticsForUser {

	/**
	 * выполняем действие
	 */
	public static function do(int $task_id, int $user_id, string $call_map, bool $end_if_not_exist_connections = true):void {

		// получаем все соединения пользователя, проверяем что соединения имеются
		$connection_list = Type_Janus_UserConnection::getAllUserConnectionsByCallMap($user_id, $call_map);
		if (count($connection_list) == 0 && $end_if_not_exist_connections) {
			throw new \cs_RowIsEmpty("not found calls connections");
		}

		// проходимся по каждому соединению и получаем информацию от janus
		self::_saveConnectionInfoFromJanus($task_id, $user_id, $call_map, $connection_list);
	}

	// получаем и сохраняем в кликхаус аналитику по соединениям пользователя
	protected static function _saveConnectionInfoFromJanus(int $task_id, int $user_id, string $call_map, array $user_connection_list):void {

		$call_room_row = Type_Janus_Room::getByCallMap($call_map);

		// проходимся по каждому соединению и собираем аналитику
		$analytics_data = [];
		foreach ($user_connection_list as $item) {

			$result = Type_Janus_Node::init($call_room_row["node_id"])->Api->sendAdminRequest("handle_info", $item["session_id"], $item["handle_id"]);
			if (isset($result["janus"]) && $result["janus"] == "error") {
				continue;
			}

			$analytics_data[] = self::_prepareAnalyticsItem($item["session_id"], $item["handle_id"], $item["is_publisher"], $result);
		}

		// собираем данные по паблишер-соединению
		$publisher_analytics = self::_getPublisherAnalyticsFromData($analytics_data);

		// получаем данные по звонку
		$meta_row = Type_Call_Meta::get($call_map);
		$call     = self::_getCallOutputData($meta_row, $user_id);

		// получаем данные по ноде
		$node = self::_getNodeOutputData($meta_row, $call_room_row["node_id"], $user_id);

		// готовим данные по подключению пользователя
		$connection = self::_getAnalyticsSummeryOutputData($publisher_analytics);

		// получаем id звонка
		$report_call_id = Type_Call_Meta::getReportCallId($meta_row["extra"]);

		Type_Call_Analytics::save($task_id, round(microtime(true) * 1000), $user_id, $report_call_id, $call_map, $call, $node, $connection, $analytics_data);
	}

	// подготавливает массив элемента с аналитикой
	#[ArrayShape(["session_id" => "int", "handle_id" => "int", "is_publisher" => "int", "analytics" => "array"])]
	protected static function _prepareAnalyticsItem(int $session_id, int $handle_id, int $is_publisher, array $analytics):array {

		// убираем из аналитики приватные данные
		$analytics = Type_Analytics_Main::doHidePrivateFields($analytics);

		return [
			"session_id"   => (int) $session_id,
			"handle_id"    => (int) $handle_id,
			"is_publisher" => (int) $is_publisher,
			"analytics"    => $analytics,
		];
	}

	// функция возвращает элемент с аналитикой publisher соединения
	protected static function _getPublisherAnalyticsFromData(array $analytics_data):array {

		$publisher_analytics = [];
		foreach ($analytics_data as $item) {

			if ($item["is_publisher"] == 1) {
				$publisher_analytics = $item;
			}
		}

		return $publisher_analytics;
	}

	/**
	 * функция подготавливает всю необходимую информацию о звонке
	 */
	#[ArrayShape(["members_count" => "int", "status" => "string", "started_at" => "int", "finished_at" => "int", "connection_duration_ms" => "int", "user_agent" => "string"])]
	protected static function _getCallOutputData(array $meta_row, int $user_id):array {

		$accepted_at_ms    = Type_Call_Users::getAcceptedAt($meta_row["users"][$user_id]);
		$established_at_ms = Type_Call_Users::getEstablishedAt($meta_row["users"][$user_id]);
		$prepared_call     = Type_Call_Utils::prepareCallForFormat($meta_row, $user_id);
		$user_agent        = Type_Call_Users::getUserAgent($meta_row["users"][$user_id]);

		return [
			"members_count"          => (int) count($prepared_call["users"]),
			"status"                 => (int) $status = Type_Call_Users::getStatus($meta_row["users"][$user_id]),
			"started_at"             => (int) $prepared_call["started_at"],
			"finished_at"            => (int) $prepared_call["finished_at"],
			"connection_duration_ms" => (int) $established_at_ms - $accepted_at_ms,
			"user_agent"             => (string) $user_agent,
		];
	}

	/**
	 * функция подготавливает всю необходимую информацию о ноде звонка
	 */
	#[ArrayShape(["node_id" => "int", "node_url" => "string", "ping" => "int"])]
	protected static function _getNodeOutputData(array $meta_row, int $node_id, int $user_id):array {

		$user_ping_result = Type_Call_Users::getPingResult($user_id, $meta_row["users"]);
		$node_ping        = 0;
		foreach ($user_ping_result as $item) {

			if ($item["node_id"] == $node_id) {
				$node_ping = $item["latency"];
			}
		}

		return [
			"node_id"  => (int) $node_id,
			"node_url" => (string) Type_Janus_Node::init($node_id)->host,
			"ping"     => (int) $node_ping,
		];
	}

	/**
	 * функция подготавливает всю необходимую инормацию о подключениях пользователя
	 */
	#[ArrayShape(["publisher_ice_candidates" => "array", "publisher_sdp" => "string"])]
	protected static function _getAnalyticsSummeryOutputData(array $publisher_analytics):array {

		return [
			"publisher_ice_candidates" => (array) ($publisher_analytics["analytics"]["info"]["streams"][0]["components"][0]["remote-candidates"] ?? []),
			"publisher_sdp"            => (string) ($publisher_analytics["analytics"]["info"]["sdps"]["remote"] ?? ""),
		];
	}
}