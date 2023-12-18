<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * класс для работы с go_talking_handler - микросервисом для общения с клиентами по websocket
 * PHP может слать запросы к go_talking_handler указывая массив пользователей которым необходимо разослать эвенты
 * либо отправит push-уведомление если пользователя нет онлайн (и PHP попросил это сделать)
 */
class Gateway_Bus_Sender {

	protected const _TOKEN_EXPIRE_TIME        = 1 * 60;      // время за которое нужно успеть авторизоваться по полученному токену
	protected const _PARENT_TYPE_CONVERSATION = "conversation"; // тип родителя - диалог

	// -------------------------------------------------------
	// WS события
	// -------------------------------------------------------

	/**
	 * отправляем событие
	 *
	 * @param Struct_Sender_Event[] $event_version_list
	 * @param array                 $user_list
	 * @param array                 $push_data
	 * @param array                 $ws_users
	 *
	 * @throws ParseFatalException
	 */
	public static function sendEvent(array $event_version_list, array $user_list, array $push_data = [], array $ws_users = []):void {

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

		self::_sendEventRequest($event_name, $user_list, $converted_event_version_list, $ws_users, $push_data);
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

	/**
	 * событие на отправку нового сообщения в диалог
	 *
	 * @param array $user_list
	 * @param array $message
	 * @param int   $messages_updated_version
	 * @param array $push_data
	 * @param array $ws_users
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationMessageReceived(array $user_list, array $message, int $messages_updated_version, array $push_data, array $ws_users):void {

		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationMessageReceived_V1::makeEvent($message, $messages_updated_version),
		], $user_list, $push_data, $ws_users);
	}

	/**
	 * событие на отправку списка сообщений в диалог
	 *
	 * @param array $user_list
	 * @param array $message_list
	 * @param int   $messages_updated_version
	 * @param array $push_data
	 * @param array $ws_users
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationMessageListReceived(array $user_list, array $message_list, int $messages_updated_version, array $push_data, array $ws_users):void {

		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationMessageListReceived_V1::makeEvent($message_list, $messages_updated_version),
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
	 *
	 */
	public static function answerDebugInfo(array $user_list, string $conversation_key, string $text):void {

		self::sendEvent([
			Gateway_Bus_Sender_Event_AnswerDebugInfo_V1::makeEvent($conversation_key, $text),
		], $user_list);
	}

	/**
	 * событие на изменение сообщения в диалоге
	 *
	 * @param array  $user_list
	 * @param string $message_map
	 * @param array  $message
	 * @param array  $prepared_message
	 * @param string $conversation_map
	 * @param array  $mention_user_id_list
	 * @param array  $diff_mentioned_user_id_list
	 * @param array  $push_data
	 * @param int    $messages_updated_version
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationMessageEdited(array $user_list, string $message_map, array $message, array $prepared_message, string $conversation_map,
									 array $mention_user_id_list, array $diff_mentioned_user_id_list, array $push_data, int $messages_updated_version):void {

		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationMessageEdited_V1::makeEvent(
				$message_map,
				$message,
				$prepared_message,
				$conversation_map,
				$mention_user_id_list,
				$messages_updated_version,
				$diff_mentioned_user_id_list
			),
		], $user_list, $push_data);
	}

	/**
	 * Отправляет событие о том, что сообщение было прочитано.
	 *
	 * @param array $user_list
	 * @param array $prepared_message
	 *
	 * @throws \cs_UnpackHasFailed
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationMessageMarkedAsRead(array $user_list, array $prepared_message):void {

		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationMessageRead_V1::makeEvent($prepared_message),
		], $user_list);
	}

	/**
	 * событие на удаление списка сообщений в диалоге
	 *
	 * @param array  $user_list
	 * @param array  $message_map_list
	 * @param string $conversation_map
	 * @param int    $messages_updated_version
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationMessageListDeleted(array $user_list, array $message_map_list, string $conversation_map, int $messages_updated_version):void {

		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationMessageListDeleted_V1::makeEvent($message_map_list, $conversation_map, $messages_updated_version),
		], $user_list);
	}

	/**
	 * событие на скрытие списка сообщений в диалоге
	 *
	 * @param array  $user_list
	 * @param array  $message_map_list
	 * @param string $conversation_map
	 * @param array  $formatted_left_menu_row
	 *
	 * @param int    $messages_updated_version
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationMessageHiddenList(array $user_list, array $message_map_list, string $conversation_map, array $formatted_left_menu_row, int $messages_updated_version):void {

		// отправляем актуальное событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationMessageListHiddenV2_V1::makeEvent(
				$message_map_list, $conversation_map, $formatted_left_menu_row, $messages_updated_version
			),
		], $user_list);
	}

	/**
	 * событие на изменение информации о ссылках
	 *
	 * @param array       $user_list
	 * @param string      $conversation_map
	 * @param string      $message_map
	 * @param array       $link_list
	 * @param int         $messages_updated_version
	 * @param string|null $preview_map
	 * @param int|null    $preview_type
	 * @param array       $preview_image
	 *
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationMessageLinkDataChanged(array $user_list, string $conversation_map, string $message_map, array $link_list, int $messages_updated_version,
										    string $preview_map = null, int $preview_type = null, array $preview_image = []):void {

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationMessageLinkDataChanged_V1::makeEvent(
				$conversation_map, $message_map, $link_list, $messages_updated_version, $preview_map, $preview_type, $preview_image
			),
		], $user_list);
	}

	/**
	 * событие на изменение статуса is_favorite для диалога
	 *
	 * @param bool  $is_favorite
	 * @param array $left_menu_row
	 * @param array $formatted_left_menu
	 *
	 * @throws ParseFatalException
	 *
	 */
	public static function conversationIsFavoriteChanged(bool $is_favorite, array $left_menu_row, array $formatted_left_menu):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($left_menu_row["user_id"], false);

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationIsFavoriteChanged_V1::makeEvent(
				$left_menu_row["conversation_map"], $is_favorite, $formatted_left_menu
			),
		], [$talking_user_schema]);
	}

	/**
	 * событие на удаление single диалога
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 *
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationSingleRemoved(int $user_id, string $conversation_map):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationSingleRemoved_V1::makeEvent($conversation_map),
		], [$talking_user_schema]);
	}

	/**
	 * событие на мьют/анмьют диалога
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param int    $is_muted
	 * @param int    $muted_until
	 *
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationMutedChanged(int $user_id, string $conversation_map, int $is_muted, int $muted_until):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationIsMutedChanged_V1::makeEvent(
				$conversation_map, $is_muted, $muted_until
			),
		], [$talking_user_schema]);
	}

	/**
	 * событие очистки сообщений диалога
	 *
	 * @param array  $user_id_list
	 * @param string $conversation_map
	 *
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationClearMessages(array $user_id_list, string $conversation_map, int $messages_updated_version):void {

		// формируем массив получателей
		$talking_user_list = [];
		foreach ($user_id_list as $user_id) {
			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($user_id, false);
		}

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationClearMessages_V1::makeEvent($conversation_map, $messages_updated_version),
		], $talking_user_list);
	}

	/**
	 * событие на прочтение диалога
	 *
	 * @param int    $user_id
	 * @param string $message_map
	 * @param array  $prepared_left_menu_row
	 * @param array  $meta
	 *
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationRead(int $user_id, string $message_map, array $prepared_left_menu_row, array $meta):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationRead_V1::makeEvent($message_map, $prepared_left_menu_row, $meta),
		], [$talking_user_schema]);
	}

	/**
	 * событие на прочтение диалога
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param array  $meta
	 *
	 * @throws ParseFatalException
	 *
	 */
	public static function conversationMarkedAsUnread(int $user_id, string $conversation_map, array $meta):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationMarkedAsUnread_V1::makeEvent($conversation_map, $meta),
		], [$talking_user_schema]);
	}

	/**
	 * событие на присоединение участника к групповому диалогу
	 *
	 * @param array  $user_list
	 * @param string $conversation_map
	 * @param int    $user_id
	 * @param int    $role
	 * @param array  $users
	 *
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationGroupUserJoined(array $user_list, string $conversation_map, int $user_id, int $role, array $users):void {

		// получаем talking_hash
		$user_id_list = self::_getUserIdListSortedByJoinTime($users);
		$talking_hash = Type_Conversation_Utils::getTalkingHash($user_id_list);

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationGroupUserJoined_V1::makeEvent(
				$conversation_map,
				$user_id,
				Apiv1_Format::getUserRole($role),
				$talking_hash,
				$user_id_list,
			),
		], $user_list, ws_users: $user_id_list);
	}

	/**
	 * событие на покидаение участником группового диалога
	 *
	 * @param array  $user_list
	 * @param string $conversation_map
	 * @param int    $user_id
	 * @param array  $users
	 * @param int    $leave_reason
	 *
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationGroupUserLeaved(array $user_list, string $conversation_map, int $user_id, array $users, int $leave_reason):void {

		// получаем talking_hash
		$user_id_list = self::_getUserIdListSortedByJoinTime($users);
		$talking_hash = Type_Conversation_Utils::getTalkingHash($user_id_list);

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationGroupUserLeaved_V1::makeEvent(
				$conversation_map,
				$user_id,
				$leave_reason,
				$talking_hash,
				$user_id_list
			),
		], $user_list, ws_users: $user_id_list);
	}

	/**
	 * событие на изменение названия группового диалога
	 *
	 * @param array  $user_list
	 * @param string $conversation_map
	 * @param string $conversation_name
	 *
	 * @throws ParseFatalException
	 *
	 */
	public static function conversationGroupRenamed(array $user_list, string $conversation_map, string $conversation_name):void {

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationGroupRenamed_V1::makeEvent($conversation_map, $conversation_name),
		], $user_list);
	}

	/**
	 * событие на изменение основной информации группового диалога (название, описание группы, аватарка)
	 *
	 * @mixed - name, description & file_map могут иметь тип false
	 *
	 * @param array        $user_list
	 * @param string       $conversation_map
	 * @param string|false $group_name
	 * @param string|false $file_map
	 * @param string|false $description
	 * @param array        $meta_row
	 *
	 * @throws ParseFatalException
	 *
	 */
	public static function conversationGroupChangedBaseInfo(array $user_list, string $conversation_map, string|false $group_name, string|false $file_map, string|false $description, array $meta_row):void {

		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationGroupInfoChanged_V1::makeEvent(
				$conversation_map,
				$group_name !== false ? $group_name : $meta_row["conversation_name"],
				$file_map !== false ? $file_map : $meta_row["avatar_file_map"],
				$description !== false ? $description : Type_Conversation_Meta_Extra::getDescription($meta_row["extra"])
			),
		], $user_list);
	}

	/**
	 * событие на изменение опций группового диалога
	 *
	 * @param array  $user_list
	 * @param string $conversation_map
	 * @param array  $actual_option_list
	 *
	 * @throws ParseFatalException
	 *
	 */
	public static function conversationGroupChangedOptions(array $user_list, string $conversation_map, array $actual_option_list):void {

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationGroupOptionsChanged_V1::makeEvent($conversation_map, $actual_option_list),
		], $user_list);
	}

	/**
	 * событие на изменение роли в групповом диалоге
	 *
	 * @param array  $user_list
	 * @param string $conversation_map
	 * @param int    $user_id
	 * @param int    $new_role
	 * @param int    $previous_role
	 *
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationGroupRoleChanged(array $user_list, string $conversation_map, int $user_id, int $new_role, int $previous_role):void {

		// получаем описание ролей пользователя
		$new_role      = Apiv1_Format::getUserRole($new_role);
		$previous_role = Apiv1_Format::getUserRole($previous_role);

		// отправляем ивент
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationGroupRoleChanged_V1::makeEvent($conversation_map, $user_id, $new_role, $previous_role),
		], $user_list, ws_users: [$user_id]);
	}

	/**
	 * событие на установление аватара в групповом диалоге
	 *
	 * @param array  $user_list
	 * @param string $conversation_map
	 * @param string $avatar_file_map
	 *
	 * @throws ParseFatalException
	 *
	 */
	public static function conversationGroupAvatarChanged(array $user_list, string $conversation_map, string $avatar_file_map):void {

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationGroupAvatarChanged_V1::makeEvent($conversation_map, $avatar_file_map),
		], $user_list);
	}

	/**
	 * событие удаления аватара в групповом диалоге
	 *
	 * @param array  $user_list
	 * @param string $conversation_map
	 *
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationGroupAvatarCleared(array $user_list, string $conversation_map):void {

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationGroupAvatarCleared_V1::makeEvent($conversation_map),
		], $user_list);
	}

	/**
	 * событие на изменение статуса приглашения
	 *
	 * @param array  $user_list
	 * @param string $invite_map
	 * @param int    $invited_user_id
	 * @param int    $status
	 * @param string $conversation_map
	 *
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationInviteStatusChanged(array $user_list, string $invite_map, int $invited_user_id, int $status, string $conversation_map):void {

		// получаем статус приглашения
		$status_title = Type_Invite_Utils::getStatusTitle($status);

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationInviteStatusChanged_V1::makeEvent($conversation_map, $invite_map, $invited_user_id, $status_title),
		], $user_list, ws_users: [$invited_user_id]);
	}

	/**
	 * событие которое появляется, когда диалог поднялся наверх
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param int    $updated_at
	 *
	 * @throws ParseFatalException
	 *
	 */
	public static function conversationLiftedUp(int $user_id, string $conversation_map, int $updated_at):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationLiftedUp_V1::makeEvent($conversation_map, $updated_at),
		], [$talking_user_schema]);
	}

	/**
	 * событие на добавление диалога
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 *
	 *
	 * @throws ParseFatalException
	 */
	public static function conversationAdded(int $user_id, string $conversation_map):void {

		// формируем talking_user_schema
		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationAdded_V1::makeEvent($conversation_map),
		], [$talking_user_schema]);
	}

	/**
	 * событие на обновление левого меню
	 *
	 * @param int   $user_id
	 * @param array $formatted_left_menu_item
	 * @param array $ws_users
	 *
	 * @throws ParseFatalException
	 *
	 */
	public static function conversationLeftMenuUpdated(int $user_id, array $formatted_left_menu_item, array $ws_users = []):void {

		$talking_user_schema = self::makeTalkingUserItem($user_id, false);

		self::sendEvent([
			Gateway_Bus_Sender_Event_ConversationLeftMenuUpdated_V1::makeEvent($formatted_left_menu_item),
		], [$talking_user_schema], ws_users: $ws_users);
	}

	/**
	 * Прочитаны все непрочитанные сообщения диалогов
	 *
	 * @param int $user_id
	 * @param int $left_menu_version
	 * @param int $filter_favorites
	 *
	 * @throws ParseFatalException
	 *
	 */
	public static function conversationsMessagesReadAll(int $user_id, int $left_menu_version, int $filter_favorites = 0):void {

		$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($user_id, false);

		self::sendEvent([
			Gateway_Bus_Sender_Event_AllConversationsMessagesRead_V1::makeEvent($left_menu_version, $filter_favorites),
		], $talking_user_list);
	}

	# region userbot

	/**
	 * ws-событие, когда был отредактирован пользовательский бот
	 *
	 * @param array $userbot
	 * @param int   $userbot_as_user_id
	 * @param array $user_id_list
	 *
	 * @throws ParseFatalException
	 *
	 */
	public static function userbotEdited(array $userbot, int $userbot_as_user_id, array $user_id_list):void {

		// формируем список пользователей на отправку ws
		$talking_user_list = [];
		foreach ($user_id_list as $v) {
			$talking_user_list[] = self::makeTalkingUserItem($v, false);
		}

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_UserbotEdited_V1::makeEvent($userbot),
		], $talking_user_list, ws_users: [$userbot_as_user_id]);
	}

	/**
	 * ws-событие, когда был включён пользовательский бот
	 *
	 * @param string $userbot_id
	 * @param int    $userbot_as_user_id
	 * @param array  $user_id_list
	 *
	 * @throws ParseFatalException
	 *
	 */
	public static function userbotEnabled(string $userbot_id, int $userbot_as_user_id, array $user_id_list):void {

		// формируем список пользователей на отправку ws
		$talking_user_list = [];
		foreach ($user_id_list as $v) {
			$talking_user_list[] = self::makeTalkingUserItem($v, false);
		}

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_UserbotEnabled_V1::makeEvent($userbot_id),
		], $talking_user_list, ws_users: [$userbot_as_user_id]);
	}

	/**
	 * ws-событие, когда был выключен пользовательский бот
	 *
	 * @param string $userbot_id
	 * @param int    $userbot_as_user_id
	 * @param array  $user_id_list
	 * @param int    $disabled_at
	 *
	 *
	 * @throws ParseFatalException
	 */
	public static function userbotDisabled(string $userbot_id, int $userbot_as_user_id, array $user_id_list, int $disabled_at):void {

		// формируем список пользователей на отправку ws
		$talking_user_list = [];
		foreach ($user_id_list as $v) {
			$talking_user_list[] = self::makeTalkingUserItem($v, false);
		}

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_UserbotDisabled_V1::makeEvent($userbot_id, $disabled_at),
		], $talking_user_list, ws_users: [$userbot_as_user_id]);
	}

	/**
	 * ws-событие, когда был удалён пользовательский бот
	 *
	 * @param string $userbot_id
	 * @param int    $userbot_as_user_id
	 * @param array  $user_id_list
	 * @param int    $deleted_at
	 *
	 *
	 * @throws ParseFatalException
	 */
	public static function userbotDeleted(string $userbot_id, int $userbot_as_user_id, array $user_id_list, int $deleted_at):void {

		// формируем список пользователей на отправку ws
		$talking_user_list = [];
		foreach ($user_id_list as $v) {
			$talking_user_list[] = self::makeTalkingUserItem($v, false);
		}

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_UserbotDeleted_V1::makeEvent($userbot_id, $deleted_at),
		], $talking_user_list, ws_users: [$userbot_as_user_id]);
	}

	/**
	 * ws-событие, когда был обновлён список команд бота
	 *
	 * @param array $userbot
	 * @param int   $userbot_as_user_id
	 * @param array $user_id_list
	 *
	 * @throws ParseFatalException
	 *
	 */
	public static function userbotCommandListUpdated(array $userbot, int $userbot_as_user_id, array $user_id_list):void {

		// формируем список пользователей на отправку ws
		$talking_user_list = [];
		foreach ($user_id_list as $v) {
			$talking_user_list[] = self::makeTalkingUserItem($v, false);
		}

		// отправляем событие
		self::sendEvent([
			Gateway_Bus_Sender_Event_UserbotCommandListUpdated_V1::makeEvent($userbot),
		], $talking_user_list, ws_users: [$userbot_as_user_id]);
	}

	# endregion userbot

	# region remind

	/**
	 * создано Напоминание
	 *
	 * @param int    $remind_id
	 * @param int    $remind_at
	 * @param int    $creator_user_id
	 * @param string $message_map
	 * @param string $comment
	 * @param string $conversation_map
	 * @param array  $talking_user_list
	 *
	 * @throws ParseFatalException
	 *
	 */
	public static function remindCreated(int $remind_id, int $remind_at, int $creator_user_id, string $message_map, string $comment, string $conversation_map, int $messages_updated_version, array $talking_user_list):void {

		self::sendEvent([
			Gateway_Bus_Sender_Event_RemindCreated_V1::makeEvent(
				$remind_id,
				$remind_at,
				$creator_user_id,
				$message_map,
				$comment,
				self::_PARENT_TYPE_CONVERSATION,
				\CompassApp\Pack\Conversation::doEncrypt($conversation_map),
				$messages_updated_version
			),
		], $talking_user_list);
	}

	/**
	 * удалено Напоминание
	 *
	 * @param int    $remind_id
	 * @param string $message_map
	 * @param string $conversation_map
	 * @param int    $messages_updated_version
	 * @param array  $talking_user_list
	 *
	 *
	 * @throws ParseFatalException
	 */
	public static function remindDeleted(int $remind_id, string $message_map, string $conversation_map, int $messages_updated_version, array $talking_user_list):void {

		self::sendEvent([
			Gateway_Bus_Sender_Event_RemindDeleted_V1::makeEvent(
				$remind_id,
				$message_map,
				self::_PARENT_TYPE_CONVERSATION,
				\CompassApp\Pack\Conversation::doEncrypt($conversation_map),
				$messages_updated_version
			),
		], $talking_user_list);
	}

	# endregion remind

	// -------------------------------------------------------
	// OTHER METHODS
	// -------------------------------------------------------

	/**
	 * делаем токен для подключения к ws по user_id
	 *
	 * @param int    $user_id
	 * @param string $token
	 * @param string $device_id
	 * @param string $platform
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function setToken(int $user_id, string $token, string $device_id = "", string $platform = Type_Api_Platform::PLATFORM_OTHER):void {

		// формируем массив для отправки
		$request = self::_prepareSetTokenParameters($user_id, $token, $platform, $device_id);

		// получаем из конфига где находится микросервис
		[, $status] = self::_doCallGrpc("SenderSetToken", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * подготавливаем массив параметров для запроса
	 *
	 * @param int    $user_id
	 * @param string $new_token
	 * @param string $platform
	 * @param string $device_id
	 *
	 * @return \SenderGrpc\SenderSetTokenRequestStruct
	 */
	protected static function _prepareSetTokenParameters(int $user_id, string $new_token, string $platform, string $device_id):\SenderGrpc\SenderSetTokenRequestStruct {

		return new \SenderGrpc\SenderSetTokenRequestStruct([
			"user_id"    => $user_id,
			"token"      => $new_token,
			"platform"   => $platform,
			"device_id"  => $device_id,
			"expire"     => time() + self::_TOKEN_EXPIRE_TIME,
			"company_id" => COMPANY_ID,
		]);
	}

	/**
	 * возвращает активные соединения пользователя
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 */
	public static function getOnlineConnectionsByUserId(int $user_id):array {

		$request = new \SenderGrpc\SenderGetOnlineConnectionsByUserIdRequestStruct([
			"user_id"    => $user_id,
			"company_id" => COMPANY_ID,
		]);

		[$response, $status] = self::_doCallGrpc("SenderGetOnlineConnectionsByUserId", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		return self::_getFormatOutput($response);
	}

	/**
	 * форматируем ответ от микросервиса
	 *
	 * @param \SenderGrpc\SenderGetOnlineConnectionsByUserIdResponseStruct $response
	 *
	 * @return array
	 */
	protected static function _getFormatOutput(\SenderGrpc\SenderGetOnlineConnectionsByUserIdResponseStruct $response):array {

		// пробегаемся по результатам ответа
		$output = [];
		foreach ($response->getOnlineConnectionList() as $item) {

			$output[] = [
				"sender_node_id" => $item->getSenderNodeId(),
				"connection_id"  => $item->getConnectionId(),
				"user_id"        => $item->getUserId(),
				"ip_address"     => $item->getIpAddress(),
				"connected_at"   => $item->getConnectedAt(),
				"user_agent"     => $item->getUserAgent(),
				"platform"       => $item->getPlatform(),
				"is_focused"     => $item->getIsFocused(),
			];
		}

		return $output;
	}

	/**
	 * закрывает активные соединения пользователя
	 *
	 * @param int $user_id
	 *
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 */
	public static function closeConnectionsByUserId(int $user_id):void {

		$request = new \SenderGrpc\SenderCloseConnectionsByUserIdRequestStruct([
			"user_id"    => $user_id,
			"company_id" => COMPANY_ID,
		]);

		[, $status] = self::_doCallGrpc("SenderCloseConnectionsByUserId", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	/**
	 * формируем talking_user_item
	 *
	 * @param int  $user_id
	 * @param bool $is_need_push
	 * @param bool $is_need_user_force_push
	 *
	 * @return int[]
	 */
	public static function makeTalkingUserItem(int $user_id, bool $is_need_push, bool $is_need_user_force_push = false):array {

		return [
			"user_id"         => $user_id,
			"need_force_push" => $is_need_user_force_push ? 1 : 0,
			"need_push"       => $is_need_push ? 1 : 0,
		];
	}

	/**
	 * формируем event_data для события добавления реакции
	 *
	 * @param string $conversation_map
	 * @param string $message_map
	 * @param string $reaction_name
	 * @param int    $user_id
	 * @param int    $updated_at_ms
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function makeEventDataForAddReaction(string $conversation_map, string $message_map, string $reaction_name, int $user_id, int $updated_at_ms):array {

		// получаем идентификатор пользователя за сеанс
		$client_launch_uuid = getClientLaunchUUID();
		if ($client_launch_uuid == "") {
			$client_launch_uuid = generateUUID();
		}

		$event_version_list = [
			Gateway_Bus_Sender_Event_ConversationMessageReactionAdded_V1::makeEvent(
				$conversation_map, $message_map, $reaction_name, $user_id, $updated_at_ms, $client_launch_uuid
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

	/**
	 * формируем event_data для события удаления реакции
	 *
	 * @param string $conversation_map
	 * @param string $message_map
	 * @param string $reaction_name
	 * @param int    $user_id
	 * @param int    $updated_at_ms
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function makeEventVersionListForRemoveReaction(string $conversation_map, string $message_map, string $reaction_name, int $user_id, int $updated_at_ms):array {

		// получаем идентификатор пользователя за сеанс
		$client_launch_uuid = getClientLaunchUUID();
		if ($client_launch_uuid == "") {
			$client_launch_uuid = generateUUID();
		}

		$event_version_list = [
			Gateway_Bus_Sender_Event_ConversationMessageReactionRemoved_V1::makeEvent(
				$conversation_map, $message_map, $reaction_name, $user_id, $updated_at_ms, $client_launch_uuid
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

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получает список user_id из массива users отсортированный по дате вступления
	 *
	 * @param array $users
	 *
	 * @return array
	 */
	protected static function _getUserIdListSortedByJoinTime(array $users):array {

		// сортируем массив
		uasort($users, function(array $a, array $b) {

			return $a["created_at"] <=> $b["created_at"];
		});

		// возвращаем только идентификаторы пользователей
		return array_keys($users);
	}

	/**
	 * отправить событие в go_sender (sender.sendEvent) через очередь rabbitMq
	 *
	 * @long - long struct
	 *
	 * @param string $event
	 * @param array  $user_list
	 * @param array  $event_version_list
	 * @param array  $ws_user_list
	 * @param array  $push_data
	 *
	 * @throws ParseFatalException
	 */
	protected static function _sendEventRequest(string $event, array $user_list, array $event_version_list, array $ws_user_list = [], array $push_data = []):void {

		// убираем дубликаты пользователей
		$user_list = self::_uniqueTalkingUserList($user_list);

		// формируем параметры задачи для rabbitMq
		$params = [
			"method"             => (string) "sender.sendEvent",
			"user_list"          => (array) $user_list,
			"event"              => (string) $event,
			"event_version_list" => (array) $event_version_list,
			"push_data"          => (object) $push_data,
			"uuid"               => (string) generateUUID(),
			"ws_users"           => (array) $ws_user_list,
			"company_id"         => COMPANY_ID,
		];

		$params = self::_prepareParams($params);

		// подготавливаем event_data (шифруем map -> key)
		$params = \CompassApp\Pack\Main::replaceMapWithKeys($params);

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
			Gateway_Bus_Rabbit::sendMessage("go_sender", $params);
		}
	}

	// -------------------------------------------------------
	// WS_USERS
	// -------------------------------------------------------

	/**
	 * формируем объект ws_users
	 *
	 * @param array $user_list
	 *
	 * @return array[]
	 */
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
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		return ShardingGateway::rpc("sender", \SenderGrpc\senderClient::class)->callGrpc($method_name, $request);
	}

	/**
	 * метод возвращает массив с уникальными пользователями (нужен только для talking_user_list)
	 *
	 * @param array $talking_user_list
	 *
	 * @return array
	 */
	protected static function _uniqueTalkingUserList(array $talking_user_list):array {

		$temp_array = [];
		foreach ($talking_user_list as $item) {

			if (!isset($temp_array[$item["user_id"]])) {
				$temp_array[$item["user_id"]] = $item;
			}
		}

		return array_values($temp_array);
	}
}