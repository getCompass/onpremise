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

	/** @var string действия по работе с аналитикой */
	protected const _ACTION_INSERT        = "insert"; // создаем аналитику по пользователю
	protected const _ACTION_UPDATE_RESULT = "update_result"; // обновляем существующую аналитику по пользователю

	/** все возможные варианты развития событий, фиксируемые в аналитике */
	protected const _RESULT_MASK_OPEN_DASHBOARD                               = 1 << 0;
	protected const _RESULT_MASK_OPEN_ENTERING_LINK                           = 1 << 1;
	protected const _RESULT_MASK_OPEN_JOIN_LINK                               = 1 << 2;
	protected const _RESULT_MASK_ACCEPT_MATCHED_JOIN_LINK                     = 1 << 3;
	protected const _RESULT_MASK_IGNORE_MATCHED_JOIN_LINK                     = 1 << 4;
	protected const _RESULT_MASK_JOIN_FIRST_SPACE                             = 1 << 5;
	protected const _RESULT_MASK_CREATE_FIRST_SPACE                           = 1 << 6;
	protected const _RESULT_MASK_LEFT_FIRST_SPACE_AFTER_ACCEPT_MATCHED_INVITE = 1 << 7;
	protected const _RESULT_MASK_WAITING_POSTMODERATION_JOIN_LINK             = 1 << 8;

	/**
	 * Нужно ли собирать аналитику по переданному пользователю
	 *
	 * @return bool
	 */
	public static function shouldCollectAnalytics(Struct_Db_PivotUser_User $user_info):bool {

		return !Domain_User_Entity_User::isQATestUser($user_info);
	}

	/**
	 * Получаем нужную константу result по client_action
	 */
	public static function resolveResultMaskByClientAction(int $client_action):int {

		return match ($client_action) {
			Domain_User_Entity_Attribution_Traffic_Abstract::CLIENT_ACTION_OPEN_DASHBOARD     => self::_RESULT_MASK_OPEN_DASHBOARD,
			Domain_User_Entity_Attribution_Traffic_Abstract::CLIENT_ACTION_OPEN_ENTERING_LINK => self::_RESULT_MASK_OPEN_ENTERING_LINK,
			Domain_User_Entity_Attribution_Traffic_Abstract::CLIENT_ACTION_OPEN_JOIN_LINK     => self::_RESULT_MASK_OPEN_JOIN_LINK,
			default                                                                           => throw new ParseFatalException("unexpected join_case = $client_action"),
		};
	}

	/**
	 * Создаем аналитику поиска совпадений среди /join/ посещений для пользователя
	 */
	public static function createUserJoinSpaceAnalytics(Struct_Dto_User_Action_Attribution_Detect_JoinSpaceAnalytics $join_space_analytics):void {

		// оптравляем данные в php-collector-server
		Gateway_Bus_CollectorAgent::init()->add(self::_JOIN_SPACE_COLLECTOR_AGENT_KEY, [
			"action"         => self::_ACTION_INSERT,
			"analytics_data" => $join_space_analytics->format(),
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

	/**
	 * Обновляем result_mask в аналитике join-space; фиксируем что пользователь принял предложенное ему приглашение
	 */
	public static function onUserAcceptMatchedJoinLink(int $user_id):void {

		Gateway_Bus_CollectorAgent::init()->add(self::_JOIN_SPACE_COLLECTOR_AGENT_KEY, [
			"action"         => self::_ACTION_UPDATE_RESULT,
			"analytics_data" => [
				"user_id"           => $user_id,
				"append_result_bit" => self::_RESULT_MASK_ACCEPT_MATCHED_JOIN_LINK,
				"remove_result_bit" => self::_RESULT_MASK_WAITING_POSTMODERATION_JOIN_LINK,
			],
		]);
	}

	/**
	 * Обновляем result_mask в аналитике join-space; фиксируем что пользователь пригнорировал предложенное ему приглашение
	 */
	public static function onUserIgnoreMatchedJoinLink(int $user_id):void {

		Gateway_Bus_CollectorAgent::init()->add(self::_JOIN_SPACE_COLLECTOR_AGENT_KEY, [
			"action"         => self::_ACTION_UPDATE_RESULT,
			"analytics_data" => [
				"user_id"           => $user_id,
				"append_result_bit" => self::_RESULT_MASK_IGNORE_MATCHED_JOIN_LINK,
				"remove_result_bit" => self::_RESULT_MASK_WAITING_POSTMODERATION_JOIN_LINK,
			],
		]);
	}

	/**
	 * Обновляем result_mask в аналитике join-space; фиксируем что пользователю вступил в свою первую команду
	 */
	public static function onUserJoinFirstSpace(int $user_id):void {

		Gateway_Bus_CollectorAgent::init()->add(self::_JOIN_SPACE_COLLECTOR_AGENT_KEY, [
			"action"         => self::_ACTION_UPDATE_RESULT,
			"analytics_data" => [
				"user_id"           => $user_id,
				"append_result_bit" => self::_RESULT_MASK_JOIN_FIRST_SPACE,
				"remove_result_bit" => self::_RESULT_MASK_WAITING_POSTMODERATION_JOIN_LINK,
			],
		]);
	}

	/**
	 * Обновляем result_mask в аналитике join-space; фиксируем что пользователю создал свою первую команду
	 */
	public static function onUserCreateFirstSpace(int $user_id):void {

		Gateway_Bus_CollectorAgent::init()->add(self::_JOIN_SPACE_COLLECTOR_AGENT_KEY, [
			"action"         => self::_ACTION_UPDATE_RESULT,
			"analytics_data" => [
				"user_id"           => $user_id,
				"append_result_bit" => self::_RESULT_MASK_CREATE_FIRST_SPACE,
				"remove_result_bit" => self::_RESULT_MASK_WAITING_POSTMODERATION_JOIN_LINK,
			],
		]);
	}

	/**
	 * Обновляем result_mask в аналитике join-space; фиксируем что пользователю покинул команду в которую ему предложили вступить
	 */
	public static function onUserLeftSpaceAfterAcceptMatchedInvite(int $user_id):void {

		Gateway_Bus_CollectorAgent::init()->add(self::_JOIN_SPACE_COLLECTOR_AGENT_KEY, [
			"action"         => self::_ACTION_UPDATE_RESULT,
			"analytics_data" => [
				"user_id"           => $user_id,
				"append_result_bit" => self::_RESULT_MASK_LEFT_FIRST_SPACE_AFTER_ACCEPT_MATCHED_INVITE,
			],
		]);
	}

	/**
	 * Обновляем result_mask в аналитике join-space; фиксируем что пользователь ожидает пост-модерации заявки
	 */
	public static function onUserWaitingPostModerationJoinLink(int $user_id):void {

		Gateway_Bus_CollectorAgent::init()->add(self::_JOIN_SPACE_COLLECTOR_AGENT_KEY, [
			"action"         => self::_ACTION_UPDATE_RESULT,
			"analytics_data" => [
				"user_id"           => $user_id,
				"append_result_bit" => self::_RESULT_MASK_WAITING_POSTMODERATION_JOIN_LINK,
			],
		]);
	}

	/**
	 * Обновляем result_mask в аналитике join-space; фиксируем что заявка с пост-модерацией была отменена
	 * Событие подходит для всех кейсов отклонения заявки с пост-модерацией
	 */
	public static function onUserPostModerationRequestCanceled(int $user_id):void {

		Gateway_Bus_CollectorAgent::init()->add(self::_JOIN_SPACE_COLLECTOR_AGENT_KEY, [
			"action"         => self::_ACTION_UPDATE_RESULT,
			"analytics_data" => [
				"user_id"           => $user_id,
				"remove_result_bit" => self::_RESULT_MASK_WAITING_POSTMODERATION_JOIN_LINK,
			],
		]);
	}

}