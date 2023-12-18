<?php

namespace Compass\Pivot;

/**
 * класс описывает работу с аналитикой поиска совпадений среди посещений /join/ страничек
 * которые привели пользователя к регистрации в приложении
 *
 * @package Compass\Pivot
 */
class Domain_User_Entity_Attribution_JoinSpaceAnalytics {

	/** @var string ключ по которому данные о связи пользователь -> посещение отправляются в php-collector-server */
	protected const _JOIN_SPACE_COLLECTOR_AGENT_KEY = "attribution_join_space_log";

	public const RESULT_NO_MATCHES_FOUND                                     = 0;
	public const RESULT_SOME_MATCHES_FOUND                                   = 1;
	public const RESULT_FULL_MATCHES_FOUND__INVITE_NOT_ACCEPTED              = 2;
	public const RESULT_FULL_MATCHES_FOUND__INVITE_ACCEPTED                  = 3;
	public const RESULT_FULL_MATCHES_FOUND__INVITE_ACCEPTED__USER_LEFT_SPACE = 8;

	/**
	 * Получаем нужную константу result с помощью % совпадений
	 *
	 * @return int
	 */
	public static function resolveResultByMatchedPercentage(int $matched_percentage):int {

		// никаких совпадений
		if ($matched_percentage === 0) {
			return self::RESULT_NO_MATCHES_FOUND;
		}

		// полное совпадение
		if ($matched_percentage === 100) {
			return self::RESULT_FULL_MATCHES_FOUND__INVITE_NOT_ACCEPTED;
		}

		// совпадение от 1 до 99%
		return self::RESULT_SOME_MATCHES_FOUND;
	}

	/**
	 * Создаем аналитику поиска совпадений среди /join/ посещений для пользователя
	 */
	public static function createUserJoinSpaceAnalytics(Struct_Dto_User_Action_Attribution_Detect_JoinSpaceAnalytics $join_space_analytics):void {

		// оптравляем данные в php-collector-server
		Gateway_Bus_CollectorAgent::init()->add(self::_JOIN_SPACE_COLLECTOR_AGENT_KEY, [
			"action"         => "insert",
			"analytics_data" => $join_space_analytics->format(),
		]);
	}

	/**
	 * Обновляем result в аналитике join-space для пользователя
	 */
	public static function updateUserJoinSpaceAnalyticsResult(int $user_id, int $result):void {

		// оптравляем данные в php-collector-server
		Gateway_Bus_CollectorAgent::init()->add(self::_JOIN_SPACE_COLLECTOR_AGENT_KEY, [
			"action"         => "update_result",
			"analytics_data" => [
				"user_id" => $user_id,
				"result"  => $result,
			],
		]);
	}

	/**
	 * Функция проверяет, что покидание команды происходит раньше чем через 1 день
	 *
	 * @return bool
	 */
	public static function isUserLeftSpaceEarly(int $join_at, int $left_at):bool {

		return $left_at < $join_at + DAY1;
	}

}