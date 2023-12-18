<?php

namespace Compass\Speaker;

/**
 * Класс для работы с мониторингом, который замеряет сеть от Janus ноды до устройства пользователя
 *
 * Class Type_Analytics_Mtr
 */
class Type_Analytics_Mtr {

	/**
	 * Получить ключ, по которому хранится информация о пользователе
	 *
	 */
	public static function getKey(int $user_id, string $call_map):string {

		return sha1($call_map) . $user_id;
	}

	/**
	 * Запустить задачу трасировки сети для участника звонка
	 * если константа IS_USER_NETWORK_TRACEROUTE_ENABLED включена
	 *
	 */
	public static function doStartMonitoring(int $user_id, string $call_map, int $node_id, string $ip_address):void {

		// если такой функционал выключен, то ничего не собираем
		if (!IS_USER_NETWORK_TRACEROUTE_ENABLED) {
			return;
		}

		$key = self::getKey($user_id, $call_map);
		try {
			Type_Janus_Node::init($node_id)->Api->doStartMtr($key, $ip_address);
		} catch (cs_FailedJanusGatewayAPIRequest) {
			Type_System_Admin::log("mtr", __METHOD__ . ": starting mtr failed");
		}
	}
}