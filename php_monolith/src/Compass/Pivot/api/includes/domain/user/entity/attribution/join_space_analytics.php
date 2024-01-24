<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

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
	 * Получаем нужную константу result по join_space_case @see Domain_User_Action_Attribution_Detect::JOIN_SPACE_CASE_OPEN_DASHBOARD, etc ...
	 *
	 * @return int
	 */
	public static function resolveResultByJoinCase(int $join_space_case):int {

		return match ($join_space_case) {
			Domain_User_Action_Attribution_Detect::JOIN_SPACE_CASE_OPEN_DASHBOARD     => self::RESULT_NO_MATCHES_FOUND,
			Domain_User_Action_Attribution_Detect::JOIN_SPACE_CASE_OPEN_ENTERING_LINK => self::RESULT_SOME_MATCHES_FOUND,
			Domain_User_Action_Attribution_Detect::JOIN_SPACE_CASE_OPEN_JOIN_LINK     => self::RESULT_FULL_MATCHES_FOUND__INVITE_NOT_ACCEPTED,
			default                                                                   => throw new ParseFatalException("unexpected join_case = $join_space_case"),
		};
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