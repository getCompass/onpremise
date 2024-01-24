<?php

namespace Compass\Speaker;

/**
 * класс для работы с аналитикой звонков
 */
class Type_Call_Analytics {

	// пишем аналитику в collector
	public static function save(int $task_id, int $created_at_ms, int $user_id, int $report_call_id, string $call_map, array $call, array $node, array $connection, array $analytics_data):void {

		[$platform, $version, $is_compass] = self::_getDataForUserAgent($call["user_agent"]);

		Gateway_Bus_CollectorAgent::init()->log("save_call_analytics", [
			"task_id"                  => $task_id,
			"created_at_ms"            => $created_at_ms,
			"event_time"               => time(),
			"user_id"                  => $user_id,
			"report_call_id"           => $report_call_id,
			"call_key"                 => Type_Pack_Call::doEncrypt($call_map),
			"members_count"            => $call["members_count"],
			"call_status"              => $call["status"],
			"started_at"               => $call["started_at"],
			"finished_at"              => $call["finished_at"],
			"connection_duration_ms"   => $call["connection_duration_ms"],
			"platform"                 => $platform,
			"version"                  => $version,
			"is_compass"               => $is_compass,
			"node_id"                  => $node["node_id"],
			"node_url"                 => $node["node_url"],
			"node_user_ping"           => $node["ping"],
			"publisher_ice_candidates" => toJson($connection["publisher_ice_candidates"]),
			"publisher_sdp"            => $connection["publisher_sdp"],
			"analytics_data"           => toJson($analytics_data),
		]);
	}

	/**
	 * получаем данные из user-agent пользователя
	 *
	 * @throws cs_PlatformNotFound
	 */
	protected static function _getDataForUserAgent(string $user_agent):array {

		$user_agent = mb_strtolower($user_agent);
		$platform   = Type_Api_Platform::getPlatform($user_agent);

		$is_compass = strstr(mb_strtolower($user_agent), "Compass") === false ? 1 : 0;

		preg_match("#\((.*?)\)#", $user_agent, $match);
		$version = $match[1];

		return [$platform, $version, $is_compass];
	}
}
