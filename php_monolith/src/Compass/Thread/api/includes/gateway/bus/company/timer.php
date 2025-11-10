<?php

namespace Compass\Thread;

/**
 * класс для работы с go_timer - микросервисом для выполнения запросов с отложенным выполнением
 */
class Gateway_Bus_Company_Timer extends Gateway_Bus_Company_Main {

	public const UPDATE_BADGE = "update_badge"; // обновления баджа

	// -------------------------------------------------------
	// WORK PUBLIC
	// -------------------------------------------------------

	/**
	 * выполяняем задачу с отложенным выполнением
	 *
	 */
	public static function setTimeout(string $method, string $uniq_key = "", array $params = [], array $extra = [], int $timeout = 5):void {

		// формируем уникальный идентификатор для задачи
		$request_key = self::_getRequestKey($method, $uniq_key);

		// отправляем задачу
		self::_sendTask($method, $request_key, $params, $extra, $timeout);
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	/**
	 * получаем параметры для обновления баджа
	 *
	 */
	public static function getParamsForUpdateBadge():array {

		return [];
	}

	/**
	 * получаем дополнительные поля для обновления баджа
	 *
	 */
	public static function getExtraForUpdateBadge(int $user_id, ?array $thread_map_list = null, ?bool $is_add_threads = null):array {

		$extra = ["user_id" => $user_id];

		if (!is_null($thread_map_list)) {

			$task_list = [];
			foreach ($thread_map_list as $map) {
				$task_list[] = "thread." . \CompassApp\Pack\Thread::doEncrypt($map);
			}

			$extra["task_list"] = $task_list;
		}

		if (!is_null($is_add_threads)) {
			$extra["is_add"] = $is_add_threads ? 1 : 0;
		}

		return $extra;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * формируем уникальный идентификатор для задачи
	 *
	 */
	protected static function _getRequestKey(string $method, string $uniq_key):string {

		if (!isEmptyString($uniq_key)) {

			return "{$method}_{$uniq_key}";
		}

		return $method;
	}

	/**
	 * отправляем задачу
	 *
	 */
	protected static function _sendTask(string $request, string $request_key, array $request_data, array $extra, int $request_timeout):void {

		// формируем параметры задачи для rabbitMq
		$params = [
			"method"       => (string) "timer.setTimeout",
			"request_name" => (string) $request,
			"request_key"  => (string) $request_key,
			"request_data" => (array) $request_data,
			"timeout"      => (int) $request_timeout,
			"company_id" => COMPANY_ID,
		];

		// добавляем к параметрам дополнительные поля из extra
		$params = array_merge($params, $extra);

		// подготавливаем event_data (шифруем map -> key)
		$params = \CompassApp\Pack\Main::replaceMapWithKeys($params);

		// проводим тест безопасности, что в ответе нет map
		\CompassApp\Pack\Main::doSecurityTest($params);

		Gateway_Bus_Rabbit::sendMessage(self::_QUEUE_NAME, $params);
	}
}