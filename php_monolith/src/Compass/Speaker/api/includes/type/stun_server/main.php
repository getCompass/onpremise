<?php

namespace Compass\Speaker;

/**
 * класс для работы с stun серверами модуля
 */
class Type_StunServer_Main {

	// сколько максимум можно получить stun серверов в ответе
	protected const _MAX_GET_STUN_LIST = 4;

	// функция для получения списка stun-серверов
	public static function getList(?array $stun_server_list = null):array {

		if (is_null($stun_server_list)) {
			$stun_server_list = getConfig("STUN_SERVER_LIST");
		}

		// если вернулся пустой список — значит stun серверов нет — возвращаем пустой массив
		if (count($stun_server_list) == 0) {
			return [];
		}

		// собираем ответ
		return self::_prepareOutput($stun_server_list);
	}

	// функция формирует ответ со списком stun серверов
	protected static function _prepareOutput(array $stun_server_list):array {

		$output = [];
		foreach ($stun_server_list as $v) {

			// если сервер выключен, то пропускаем
			if ($v["is_enabled"] == 0) {
				continue;
			}

			$output[] = "stun:{$v["host"]}:{$v["port"]}";

			// если набрали максимум серверов для ответа
			if (count($output) == self::_MAX_GET_STUN_LIST) {
				break;
			}
		}

		return $output;
	}
}