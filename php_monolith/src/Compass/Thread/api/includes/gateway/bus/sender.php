<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use Compass\Conversation\Struct_Sender_Event;

/**
 * класс для работы с go_talking_handler - микросервисом для общения с клиентами по websocket
 * PHP может слать запросы к go_talking_handler указывая массив пользователей которым необходимо разослать эвенты
 * либо отправит push-уведомление если пользователя нет онлайн (и PHP попросил это сделать)
 */
class Gateway_Bus_Sender {

	protected const _PARENT_TYPE_THREAD = "thread";  // тип родителя - тред

	/**
	 * проверяем параметры
	 *
	 * @param Struct_Sender_Event[] $event_version_list
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

	// -------------------------------------------------------
	// WS события
	// -------------------------------------------------------

	/**
	 * новое сообщение в треде
	 *
	 * @param array  $user_list
	 * @param array  $message
	 * @param array  $push_data
	 * @param array  $thread_meta
	 * @param array  $ws_users
	 * @param array  $follower_list
	 * @param string $location_type
	 * @param string $conversation_map
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function threadMessageReceived(array $user_list, array $message, array $push_data, array $thread_meta, array $ws_users, array $follower_list, string $location_type, string $conversation_map, int $threads_updated_version):void {

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_ThreadMessageReceived_V1::makeEvent(
				$message, $thread_meta, $follower_list, $location_type, $conversation_map, $threads_updated_version
			),
		], $user_list, $push_data, $ws_users);
	}

	/**
	 * новое сообщения в треде
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function threadMessageListReceived(array $user_list, array $message_list, array $push_data, array $thread_meta, array $ws_users, array $follower_list, string $location_type, string $conversation_map, int $threads_updated_version):void {

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_ThreadMessageListReceived_V1::makeEvent(
				$message_list, $thread_meta, $follower_list, $location_type, $conversation_map, $threads_updated_version
			),
		], $user_list, $push_data, $ws_users);
	}

	/**
	 * событие о сохранении времени ответа
	 *
	 * @param array  $user_list
	 * @param string $conversation_key
	 * @param string $text
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function answerDebugInfo(array $user_list, string $conversation_key, string $text):void {

		self::_sendEvent([
			Gateway_Bus_Sender_Event_AnswerDebugInfo_V1::makeEvent($conversation_key, $text),
		], $user_list);
	}

	/**
	 * сообщение в треде было отредактировано
	 *
	 * @param array  $user_list
	 * @param array  $message
	 * @param string $message_map
	 * @param string $new_text
	 * @param int    $last_message_text_edited_at
	 * @param array  $mention_user_id_list
	 * @param array  $diff_mentioned_user_id_list
	 * @param array  $push_data
	 * @param array  $follower_list
	 * @param string $location_type
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function threadMessageEdited(array $user_list, array $message, string $message_map, string $new_text, int $last_message_text_edited_at, array $mention_user_id_list, array $diff_mentioned_user_id_list, array $push_data, array $follower_list, string $location_type):void {

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_ThreadMessageEdited_V1::makeEvent(
				$message_map,
				$new_text,
				$last_message_text_edited_at,
				arrayValuesInt($mention_user_id_list),
				arrayValuesInt($diff_mentioned_user_id_list),
				Apiv1_Format::threadMessage($message),
				$follower_list,
				$location_type,
			),
		], $user_list, $push_data);
	}

	/**
	 * скрыто сообщение в треде
	 *
	 * @param array  $user_list
	 * @param string $thread_map
	 * @param array  $message_map_list
	 * @param string $conversation_map
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function threadMessageListHidden(array $user_list, string $thread_map, array $message_map_list, string $conversation_map, int $threads_updated_version):void {

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_ThreadMessageListHidden_V1::makeEvent($message_map_list, $thread_map, $conversation_map, $threads_updated_version),
		], $user_list);
	}

	/**
	 * событие на удаление списка сообщений в треде
	 *
	 * @param array  $user_list
	 * @param array  $message_map_list
	 * @param string $thread_map
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function threadMessageListDeleted(array $user_list, array $message_map_list, string $thread_map):void {

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_ThreadMessageListDeleted_V1::makeEvent($message_map_list, $thread_map),
		], $user_list);
	}

	/**
	 * к сообщению был прикреплен тред
	 *
	 * @param array  $user_list
	 * @param array  $thread_meta
	 * @param string $message_map
	 * @param string $thread_map
	 * @param string $conversation_map
	 * @param string $routine_key
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function conversationMessageThreadAttached(array $user_list, array $thread_meta, string $message_map, string $thread_map, string $conversation_map, string $routine_key):void {

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_ConversationMessageThreadAttached_V1::makeEvent($thread_meta, $message_map, $thread_map, $conversation_map),
		], $user_list, routine_key: $routine_key);
	}

	/**
	 * отправляем событие о том, что к заявке найма был привязан тред
	 *
	 * @param array  $user_list
	 * @param int    $hiring_request_id
	 * @param array  $thread_meta_row
	 * @param string $thread_map
	 * @param string $routine_key
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function hiringRequestThreadAttached(array $user_list, int $hiring_request_id, array $thread_meta_row, string $thread_map, string $routine_key):void {

		self::_sendEvent([
			Gateway_Bus_Sender_Event_HiringRequestThreadAttached_V1::makeEvent($hiring_request_id, $thread_map, $thread_meta_row),
		], $user_list, routine_key: $routine_key);
	}

	/**
	 * отправляем событие о том, что к заявке увольнения был привязан тред
	 *
	 * @param array  $user_list
	 * @param int    $dismissal_request_id
	 * @param array  $thread_meta_row
	 * @param string $thread_map
	 * @param string $routine_key
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function dismissalRequestThreadAttached(array $user_list, int $dismissal_request_id, array $thread_meta_row, string $thread_map, string $routine_key):void {

		self::_sendEvent([
			Gateway_Bus_Sender_Event_DismissalRequestThreadAttached_V1::makeEvent($dismissal_request_id, $thread_map, $thread_meta_row),
		], $user_list, routine_key: $routine_key);
	}

	/**
	 * тред был прочитан
	 *
	 * @param int    $user_id
	 * @param string $thread_map
	 * @param string $message_map
	 * @param string $conversation_map
	 * @param array  $total_unread_count
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function threadRead(int $user_id, string $thread_map, string $message_map, string $conversation_map, array $total_unread_count, int $threads_updated_version):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_ThreadRead_V1::makeEvent($thread_map, $conversation_map, $message_map, $total_unread_count, $threads_updated_version),
		], [$talking_user_schema]);
	}

	/**
	 * тред был помечен как непрочитанный
	 *
	 * @param int    $user_id
	 * @param string $thread_map
	 * @param string $conversation_map
	 * @param array  $total_unread_count
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function threadMarkedAsUnread(int $user_id, string $thread_map, string $conversation_map, array $total_unread_count, int $threads_updated_version):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_ThreadMarkedAsUnread_V1::makeEvent($thread_map, $conversation_map, $total_unread_count, $threads_updated_version),
		], [$talking_user_schema]);
	}

	/**
	 * пользователь отписался от треда
	 *
	 * @param int    $user_id
	 * @param string $thread_map
	 * @param array  $total_unread_count
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function threadUnfollow(int $user_id, string $thread_map, array $total_unread_count):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_ThreadUnfollow_V1::makeEvent($thread_map, $total_unread_count),
		], [$talking_user_schema]);
	}

	/**
	 * пользователь подписался на тред
	 *
	 * @param int    $user_id
	 * @param string $thread_map
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function threadFollow(int $user_id, string $thread_map):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_ThreadFollow_V1::makeEvent($thread_map),
		], [$talking_user_schema]);
	}

	/**
	 * событие на изменение информации о ссылках
	 *
	 * @param array       $user_list
	 * @param string      $message_map
	 * @param array       $link_list
	 * @param int         $threads_updated_version
	 * @param string|null $preview_map
	 * @param int|null    $preview_type
	 * @param array       $preview_image
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function threadMessageLinkDataChanged(array $user_list, string $message_map, array $link_list, int $threads_updated_version, string $preview_map = null, int $preview_type = null, array $preview_image = []):void {

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_ThreadMessageLinkDataChanged_V1::makeEvent($message_map, $link_list, $threads_updated_version, $preview_map, $preview_type, $preview_image),
		], $user_list);
	}

	/**
	 * событие на изменение статуса is_muted для треда
	 *
	 * @param int    $user_id
	 * @param string $thread_map
	 * @param bool   $is_muted
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function threadIsMutedChanged(int $user_id, string $thread_map, bool $is_muted):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_ThreadIsMutedChanged_V1::makeEvent($thread_map, $is_muted),
		], [$talking_user_schema]);
	}

	/**
	 * Отсылает веб-сокет событие о том, что был изменен элемент из тред-меню.
	 *
	 * @param int   $user_id
	 * @param array $prepared_thread_menu_item
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function threadMenuItemUpdated(int $user_id, array $prepared_thread_menu_item):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// подготавливаем для фронта
		$formatted_left_menu_item = Apiv1_Format::threadMenu($prepared_thread_menu_item);

		// отправляем событие
		self::_sendEvent([
			Gateway_Bus_Sender_Event_ThreadMenuItemUpdated_V1::makeEvent($formatted_left_menu_item),
		], [$talking_user_schema]);
	}

	/**
	 * Прочитаны все непрочитанные сообщения тредов
	 *
	 * @param int $user_id
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function threadsMessagesReadAll(int $user_id):void {

		$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($user_id, false);

		self::_sendEvent([
			Gateway_Bus_Sender_Event_AllThreadsMessagesRead_V1::makeEvent(),
		], $talking_user_list);
	}

	/**
	 * событие на изменение статуса is_favorite для треда
	 *
	 * @param int    $user_id
	 * @param string $thread_map
	 * @param bool   $is_favorite
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function threadIsFavoriteChanged(int $user_id, string $thread_map, bool $is_favorite):void {

		// формируем talking_user_schema
		$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($user_id, false);

		self::_sendEvent([
			Gateway_Bus_Sender_Event_ThreadIsFavoriteChanged_V1::makeEvent($thread_map, $is_favorite ? 1 : 0),
		], $talking_user_list);
	}

	/**
	 * создано Напоминание
	 *
	 * @param int    $remind_id
	 * @param int    $remind_at
	 * @param int    $creator_user_id
	 * @param string $message_map
	 * @param string $comment
	 * @param string $thread_map
	 * @param array  $talking_user_list
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function remindCreated(int $remind_id, int $remind_at, int $creator_user_id, string $message_map, string $comment, string $thread_map, array $talking_user_list):void {

		self::_sendEvent([
			Gateway_Bus_Sender_Event_RemindCreated_V1::makeEvent(
				$remind_id,
				$remind_at,
				$creator_user_id,
				$message_map,
				$comment,
				self::_PARENT_TYPE_THREAD,
				\CompassApp\Pack\Thread::doEncrypt($thread_map),
			),
		], $talking_user_list);
	}

	/**
	 * удалено Напоминание
	 *
	 * @param int    $remind_id
	 * @param string $message_map
	 * @param string $thread_map
	 * @param array  $talking_user_list
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function remindDeleted(int $remind_id, string $message_map, string $thread_map, array $talking_user_list):void {

		self::_sendEvent([
			Gateway_Bus_Sender_Event_RemindDeleted_V1::makeEvent(
				$remind_id,
				$message_map,
				self::_PARENT_TYPE_THREAD,
				\CompassApp\Pack\Thread::doEncrypt($thread_map),
			),
		], $talking_user_list);
	}

	// -------------------------------------------------------
	// OTHER METHODS
	// -------------------------------------------------------

	// пользователь открыл тред, а значит его необходимо добавить к треду
	// принимает массив с идентификаторами пользователей user_list ([1,2,3,4])
	public static function addUsersToThread(array $user_list, string $thread_map, string $routine_key = ""):void {

		$grpcRequest = new \SenderGrpc\SenderAddUsersToThreadRequestStruct([
			"thread_key"  => \CompassApp\Pack\Thread::doEncrypt($thread_map),
			"user_list"   => $user_list,
			"routine_key" => $routine_key,
			"company_id"  => COMPANY_ID,
		]);

		// отправляем задачу в grpc
		/** @noinspection PhpParamsInspection $grpc_request что ты такое? */
		[, $status] = self::_doCallGrpc("SenderAddUsersToThread", $grpcRequest);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	// формируем talking_user_item
	public static function makeTalkingUserItem(int $user_id, bool $is_need_push, bool $is_need_user_force_push = false):array {

		return [
			"user_id"         => $user_id,
			"need_force_push" => $is_need_user_force_push ? 1 : 0,
			"need_push"       => $is_need_push ? 1 : 0,
		];
	}

	// формируем event_data для события добавления реакции
	public static function makeEventDataForAddReaction(string $message_map, string $reaction_name, int $user_id, int $updated_at_ms):array {

		// получаем идентификатор пользователя за сеанс
		$client_launch_uuid = getClientLaunchUUID();
		if ($client_launch_uuid == "") {
			$client_launch_uuid = generateUUID();
		}

		$event_version_list = [
			Gateway_Bus_Sender_Event_ThreadMessageReactionAdded_V1::makeEvent(
				$message_map, $reaction_name, $user_id, $updated_at_ms, $client_launch_uuid
			),
		];

		// здесь же переводим из структуры в массив
		$converted_event_version_list = self::_convertStructEventToArrayEvent($event_version_list);

		// подготавливаем event_data (шифруем map -> key)
		$converted_event_version_list = \CompassApp\Pack\Main::replaceMapWithKeys($converted_event_version_list);

		// проводим тест безопасности, что в ответе нет map
		\CompassApp\Pack\Main::doSecurityTest($converted_event_version_list);

		return $converted_event_version_list;
	}

	// формируем event_data для события удаления реакции
	public static function makeEventDataForRemoveReaction(string $message_map, string $reaction_name, int $user_id, int $updated_at_ms):array {

		// получаем идентификатор пользователя за сеанс
		$client_launch_uuid = getClientLaunchUUID();
		if ($client_launch_uuid == "") {
			$client_launch_uuid = generateUUID();
		}

		$event_version_list = [
			Gateway_Bus_Sender_Event_ThreadMessageReactionRemoved_V1::makeEvent($message_map, $reaction_name, $user_id, $updated_at_ms, $client_launch_uuid),
		];

		// здесь же переводим из структуры в массив
		$converted_event_version_list = self::_convertStructEventToArrayEvent($event_version_list);

		// подготавливаем event_data (шифруем map -> key)
		$converted_event_version_list = \CompassApp\Pack\Main::replaceMapWithKeys($converted_event_version_list);

		// проводим тест безопасности, что в ответе нет map
		\CompassApp\Pack\Main::doSecurityTest($converted_event_version_list);

		return $converted_event_version_list;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * отправляем событие
	 *
	 * @param Struct_Sender_Event[] $event_version_list
	 * @param array                 $user_list
	 * @param array                 $push_data
	 * @param array                 $ws_users
	 * @param string                $routine_key
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	protected static function _sendEvent(array $event_version_list, array $user_list, array $push_data = [], array $ws_users = [], string $routine_key = ""):void {

		// проверяем что прислали корректные параметры
		self::_assertSendEventParameters($event_version_list);

		// если прислали пустой массив получателей
		if (count($user_list) < 1) {

			// ничего не делаем
			return;
		}

		// получаем название событие
		$event_name = $event_version_list[0]->event;

		// конвертируем event_version_list из структуру в массив
		$converted_event_version_list = self::_convertStructEventToArrayEvent($event_version_list);

		self::_sendEventRequest($event_name, $user_list, $converted_event_version_list, $ws_users, $push_data, $routine_key);
	}

	/**
	 * конвертируем события из структуры Struct_Sender_Event в массив
	 *
	 * @param Struct_Sender_Event[] $struct_event_version_list
	 *
	 * @return array
	 */
	protected static function _convertStructEventToArrayEvent(array $struct_event_version_list):array {

		$converted_event_version_list = [];
		foreach ($struct_event_version_list as $event) {

			$converted_event_version_list[] = [
				"version" => (int) $event->version,
				"data"    => (object) $event->ws_data,
			];
		}

		return $converted_event_version_list;
	}

	/**
	 * отправить событие в go_sender (sender.sendEvent) через очередь rabbitMq
	 *
	 * @param string $event
	 * @param array  $user_list
	 * @param array  $event_version_list
	 * @param array  $ws_user_list
	 * @param array  $push_data
	 * @param string $routine_key
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @long
	 */
	protected static function _sendEventRequest(string $event, array $user_list, array $event_version_list, array $ws_user_list = [], array $push_data = [], string $routine_key = ""):void {

		// формируем параметры задачи для rabbitMq
		$params = [
			"method"             => (string) "sender.sendEvent",
			"user_list"          => (array) $user_list,
			"event"              => (string) $event,
			"event_version_list" => (array) $event_version_list,
			"push_data"          => (object) $push_data,
			"uuid"               => (string) generateUUID(),
			"ws_users"           => (array) $ws_user_list,
			"routine_key"        => (string) $routine_key,
			"company_id"         => COMPANY_ID,
		];

		$params = self::_prepareParams($params);

		// проводим тест безопасности, что в ответе нет map
		\CompassApp\Pack\Main::doSecurityTest($params);

		$converted_user_list          = self::_convertReceiverUserListToGrpcStructure($user_list);
		$converted_event_version_list = self::_convertEventVersionListToGrpcStructure($params["event_version_list"]);
		$grpc_request                 = new \SenderGrpc\SenderSendEventRequestStruct([
			"user_list"          => $converted_user_list,
			"event"              => $params["event"],
			"event_version_list" => $converted_event_version_list,
			"push_data"          => toJson($params["push_data"]),
			"uuid"               => $params["uuid"],
			"ws_users"           => isset($params["ws_users"]) ? toJson($params["ws_users"]) : "",
			"company_id"         => COMPANY_ID,
		]);

		/** @noinspection PhpParamsInspection $grpc_request что ты такое? */
		self::_sendRequestWrap("SenderSendEvent", $grpc_request, $params);
	}

	/**
	 * подготавливаем $params
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	protected static function _prepareParams(array $params):array {

		// добавляем к параметрам задачи
		$params["ws_users"] = (object) self::makeUsers($params["ws_users"]);

		// подготавливаем event_data (шифруем map -> key)
		$params = \CompassApp\Pack\Main::replaceMapWithKeys($params);

		return $params;
	}

	/**
	 * конвертируем user_list в структуру понятную grpc
	 *
	 * @param array $user_list
	 *
	 * @return array
	 */
	protected static function _convertReceiverUserListToGrpcStructure(array $user_list):array {

		$output = [];
		foreach ($user_list as $user_item) {

			$output[] = new \SenderGrpc\EventUserStruct([
				"user_id"         => $user_item["user_id"],
				"need_force_push" => $user_item["need_force_push"],
				"need_push"       => $user_item["need_push"],
			]);
		}

		return $output;
	}

	/**
	 * конвертируем event_version_list в структуру понятную grpc
	 *
	 * @param array $event_version_list
	 *
	 * @return array
	 */
	protected static function _convertEventVersionListToGrpcStructure(array $event_version_list):array {

		$output = [];
		foreach ($event_version_list as $event_version_item) {

			$output[] = new \SenderGrpc\EventVersionItem([
				"version" => (int) $event_version_item["version"],
				"data"    => toJson((object) $event_version_item["data"]),
			]);
		}

		return $output;
	}

	/**
	 * обертка для отправки запроса с возможностью переоправки через асинхронный канал при неудаче
	 *
	 * @param string                            $grpc_method_name
	 * @param \Google\Protobuf\Internal\Message $grpc_request
	 * @param array                             $params
	 *
	 * @noinspection PhpUndefinedClassInspection \Google\Protobuf\Internal\Message что ты такое?
	 * @noinspection PhpUndefinedNamespaceInspection \Google\Protobuf\Internal\Message что ты такое?
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	protected static function _sendRequestWrap(string $grpc_method_name, \Google\Protobuf\Internal\Message $grpc_request, array $params):void {

		try {

			[, $status] = self::_doCallGrpc($grpc_method_name, $grpc_request);
			if ($status->code !== \Grpc\STATUS_OK) {
				throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
			}
		} catch (\Error | BusFatalException) {

			Type_System_Admin::log("go_sender", "go_sender call grpc on {$grpc_method_name}");

			// отправляем задачу в rabbitMq
			ShardingGateway::rabbit()->sendMessage("go_sender", $params);
		}
	}

	// -------------------------------------------------------
	// WS_USERS
	// -------------------------------------------------------

	// формируем объект ws_users
	public static function makeUsers(array $user_list):array {

		// если user_list пустой, отдаем пустой массив
		if (count($user_list) < 1) {

			return [
				"user_list" => [],
			];
		}

		// если вдруг есть смещение ключей в массиве, убираем
		$user_list = array_values($user_list);

		// принудительно приводим id пользователя к int
		$user_list = arrayValuesInt($user_list);

		return [
			"user_list" => (array) $user_list,
			"signature" => (string) Type_Api_Action::getUsersSignature($user_list, time()),
		];
	}

	/**
	 * делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @param string                            $method_name
	 * @param \Google\Protobuf\Internal\Message $request
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @noinspection PhpUndefinedNamespaceInspection \Google\Protobuf\Internal\Message что ты такое?
	 * @noinspection PhpUndefinedClassInspection \Google\Protobuf\Internal\Message что ты такое
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		return ShardingGateway::rpc("sender", \SenderGrpc\senderClient::class)->callGrpc($method_name, $request);
	}
}