<?php

namespace Compass\Company;

/**
 * Класс для работы с go_timer - микросервисом для выполнения запросов с отложенным выполнением
 */
class Gateway_Bus_Company_Timer extends Gateway_Bus_Company_Main {

	public const UPDATE_BADGE      = "update_badge"; // обновления баджа
	public const HIBERNATE_COMPANY = "hibernate_company"; // усыпить компанию

	// -------------------------------------------------------
	// WORK PUBLIC
	// -------------------------------------------------------

	/**
	 * выполяняем задачу с отложенным выполнением
	 */
	public static function setTimeout(string $method, string $uniq_key = "", array $params = [], array $extra = [], int $timeout = 5):void {

		// формируем уникальный идентификатор для задачи
		$request_key = self::_getRequestKey($method, $uniq_key);

		// отправляем задачу
		self::_sendTask($method, $request_key, $params, $extra, $timeout);
	}

	/**
	 * Выполяняем задачу из таймера незамедлительно
	 */
	public static function doForceWork(string $method, string $uniq_key = ""):void {

		// формируем уникальный идентификатор для задачи
		$request_key = self::_getRequestKey($method, $uniq_key);

		// отправляем задачу
		$params = [
			"method"      => (string) "timer.doForceWork",
			"request_key" => (string) $request_key,
			"company_id"  => COMPANY_ID,
		];

		Gateway_Bus_Rabbit::sendMessage(self::_QUEUE_NAME, $params);
	}

	/**
	 * Выполяняем задачу из таймера незамедлительно
	 */
	public static function deleteTasks():void {

		$params = [
			"method" => (string) "timer.deleteTaskCache",
		];

		Gateway_Bus_Rabbit::sendMessage(self::_QUEUE_NAME, $params);
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	/**
	 * получаем параметры для обновления баджа
	 */
	public static function getParamsForUpdateBadge():array {

		return [];
	}

	/**
	 * получаем дополнительные поля для обновления баджа
	 */
	public static function getExtraForUpdateBadge(int $user_id):array {

		return ["user_id" => $user_id];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * формируем уникальный идентификатор для задачи
	 */
	protected static function _getRequestKey(string $method, string $uniq_key):string {

		if (!isEmptyString($uniq_key)) {

			return "{$method}_{$uniq_key}";
		}

		return $method;
	}

	/**
	 * отправляем задачу
	 */
	protected static function _sendTask(string $request, string $request_key, array $request_data, array $extra, int $request_timeout):void {

		// формируем параметры задачи для rabbitMq
		$params = [
			"method"       => (string) "timer.setTimeout",
			"request_name" => (string) $request,
			"request_key"  => (string) $request_key,
			"request_data" => (array) $request_data,
			"timeout"      => (int) $request_timeout,
			"company_id"   => COMPANY_ID,
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