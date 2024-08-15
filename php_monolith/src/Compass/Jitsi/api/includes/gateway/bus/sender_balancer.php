<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Class Gateway_Bus_SenderBalancer
 */
class Gateway_Bus_SenderBalancer {

	/**
	 * Отправка события о создании конференции
	 *
	 * @param int                               $user_id
	 * @param Struct_Api_Conference_Data        $conference_data
	 * @param Struct_Api_Conference_JoiningData $conference_joining_data
	 * @param Struct_Api_Conference_MemberData  $conference_member_data
	 * @param Struct_Api_Conference_CreatorData $conference_creator_data
	 * @param array                             $push_data
	 * @param array                             $ws_users
	 *
	 * @throws ParseFatalException
	 */
	public static function conferenceCreated(int                               $user_id, Struct_Api_Conference_Data $conference_data,
							     Struct_Api_Conference_JoiningData $conference_joining_data,
							     Struct_Api_Conference_MemberData  $conference_member_data,
							     Struct_Api_Conference_CreatorData $conference_creator_data,
							     array                             $push_data, array $ws_users = []):void {

		$event_version_item = Gateway_Bus_SenderBalancer_Event_ConferenceCreated_V1::makeEvent(
			$conference_data, $conference_joining_data, $conference_member_data, $conference_creator_data
		);

		// конвертируем event_version_list из структуру в массив
		$converted_event_version_list[] = [
			"version" => (int) $event_version_item->version,
			"data"    => (object) $event_version_item->ws_data,
		];

		self::_sendJitsiConferenceCreated($user_id, $event_version_item->event, $converted_event_version_list, $ws_users, $push_data);
	}

	/**
	 * Отправка события в go_sender_balancer
	 *
	 * @param int    $user_id
	 * @param string $event
	 * @param array  $event_version_list
	 * @param array  $ws_users
	 * @param array  $push_data
	 *
	 * @throws ParseFatalException
	 */
	protected static function _sendJitsiConferenceCreated(int $user_id, string $event, array $event_version_list, array $ws_users = [], array $push_data = []):void {

		$params = [
			"method"             => (string) "talking.jitsiConferenceCreated",
			"user_id"            => (int) $user_id,
			"event"              => (string) $event,
			"event_version_list" => (array) $event_version_list,
			"push_data"          => (object) $push_data,
			"uuid"               => (string) generateUUID(),
			"ws_users"           => (array) $ws_users,
			"time_to_live"       => (int) Domain_PhpJitsi_Entity_Event_NeedCheckSingleConference::NEED_WORK_INTERVAL,
		];

		// отправляем задачу в rabbitMq
		ShardingGateway::rabbit()->sendMessage(GO_SENDER_BALANCER_QUEUE, $params);
	}

	/**
	 * Отправить VoIP push
	 *
	 * @param int   $user_id
	 * @param array $push_data
	 *
	 * @throws ParseFatalException
	 */
	public static function sendVoIPPush(int $user_id, array $push_data):void {

		$params = [
			"method"    => "talking.sendJitsiVoIPPush",
			"user_id"   => $user_id,
			"push_data" => $push_data,
			"uuid"      => generateUUID(),
		];

		// отправляем задачу в rabbitMq
		ShardingGateway::rabbit()->sendMessage(GO_SENDER_BALANCER_QUEUE, $params);
	}

	/**
	 * Отправка события об изменении данных профиля
	 *
	 * @param array                                  $user_id_list
	 * @param string                                 $action
	 * @param Struct_Api_Conference_Data             $conference_data
	 * @param Struct_Api_Conference_MemberData|null  $conference_member_data
	 * @param Struct_Api_Conference_JoiningData|null $conference_joining_data
	 * @param int|null                               $conference_active_at
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function activeConferenceUpdated(array                              $user_id_list, string $action, Struct_Api_Conference_Data $conference_data,
								     ?Struct_Api_Conference_MemberData  $conference_member_data,
								     ?Struct_Api_Conference_JoiningData $conference_joining_data,
								     ?int                               $conference_active_at):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_ActiveConferenceUpdated_V1::makeEvent(
				$action, $conference_data, $conference_member_data, $conference_joining_data, $conference_active_at
			),
		], $user_id_list);
	}

	/**
	 * Отправка события об изменении параметров конференции
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function conferenceOptionsUpdated(array $user_id_list, Struct_Api_Conference_Data $conference_data):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_ConferenceOptionsUpdated_V1::makeEvent($conference_data),
		], $user_id_list);
	}

	/**
	 * Отправка события об игнорировании сингла
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function conferenceAcceptStatusUpdated(string $conference_id, string $accept_status, int $user_id, array $conference_user_id_list):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_ConferenceAcceptStatusUpdated_V1::makeEvent($conference_id, $user_id, $accept_status),
		], $conference_user_id_list);
	}
	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * отправляем событие
	 *
	 * @param Struct_SenderBalancer_Event[] $event_version_list
	 * @param array                         $user_id_list
	 * @param array                         $ws_users
	 * @param array                         $push_data
	 * @param int                           $is_need_push
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	protected static function _sendEvent(array $event_version_list, array $user_id_list, array $ws_users = [], array $push_data = [], int $is_need_push = 0):void {

		// проверяем что прислали корректные параметры
		self::_assertSendEventParameters($event_version_list);

		// если прислали пустой массив получателей
		if (count($user_id_list) < 1) {

			// ничего не делаем
			return;
		}

		// получаем название событие
		$event_name = $event_version_list[0]->event;

		// конвертируем event_version_list из структуру в массив
		$converted_event_version_list = [];
		foreach ($event_version_list as $event) {

			$converted_event_version_list[] = [
				"version" => (int) $event->version,
				"data"    => (object) $event->ws_data,
			];
		}

		self::_sendEventRequest($user_id_list, $event_name, $converted_event_version_list, $ws_users, $push_data, $is_need_push);
	}

	/**
	 * проверяем параметры
	 *
	 * @param Struct_SenderBalancer_Event[] $event_version_list
	 *
	 * @throws ParseFatalException
	 */
	protected static function _assertSendEventParameters(array $event_version_list):void {

		// если прислали пустой массив версий метода
		if (count($event_version_list) < 1) {
			throw new ParseFatalException("incorrect array event version list");
		}

		// проверяем, что все версии события описывают один и тот же метод
		$ws_method_name = $event_version_list[0]->event;
		foreach ($event_version_list as $event) {

			if ($event->event !== $ws_method_name) {
				throw new ParseFatalException("different ws event names");
			}
		}
	}

	/**
	 * Отправка события в go_sender_balancer
	 *
	 * @param array  $user_id_list
	 * @param string $event
	 * @param array  $event_version_list
	 * @param array  $ws_users
	 * @param array  $push_data
	 * @param int    $is_need_push
	 *
	 * @throws \parseException
	 */
	protected static function _sendEventRequest(array $user_id_list, string $event, array $event_version_list, array $ws_users = [], array $push_data = [], int $is_need_push = 0):void {

		// формируем массив для отправки
		$user_list = [];
		foreach ($user_id_list as $user_id) {
			$user_list[] = self::makeTalkingUserItem($user_id);
		}

		// формируем параметры задачи для rabbitMq
		$params = [
			"method"             => (string) "talking.sendEvent",
			"user_list"          => (array) $user_list,
			"event"              => (string) $event,
			"event_version_list" => (array) $event_version_list,
			"push_data"          => (object) $push_data,
			"is_need_push"       => (int) $is_need_push,
			"uuid"               => (string) generateUUID(),
			"ws_users"           => (array) $ws_users,
		];

		// отправляем задачу в rabbitMq
		ShardingGateway::rabbit()->sendMessage(GO_SENDER_BALANCER_QUEUE, $params);
	}

	/**
	 * Формируем объект talking_user_item
	 *
	 * @param int $user_id
	 *
	 * @return int[]
	 */
	public static function makeTalkingUserItem(int $user_id):array {

		return [
			"user_id" => $user_id,
		];
	}
}