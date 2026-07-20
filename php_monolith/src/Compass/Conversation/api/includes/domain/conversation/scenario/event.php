<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Агрегатор подписок на событие для домена conversation.
 * Класс обработки сценариев событий.
 */
class Domain_Conversation_Scenario_Event
{
	/**
	 * Callback для события изменения имени диалога.
	 */
	#[Type_Attribute_EventListener(Type_Event_Conversation_ChangeConversationName::EVENT_TYPE)]
	public static function onChangeConversationName(Struct_Event_Conversation_ChangeConversationName $event_data): void
	{

		// проходимся по всем участникам диалога
		foreach ($event_data->users as $k => $_) {

			// обновляем название группового диалога в записях пользователя
			Type_Conversation_LeftMenu::onChangeName(
				$k,
				$event_data->conversation_map,
				$event_data->conversation_name
			);
		}
	}

	/**
	 * Callback для события изменения аватарки диалога.
	 */
	#[Type_Attribute_EventListener(Type_Event_Conversation_ChangeConversationAvatar::EVENT_TYPE)]
	public static function onChangeConversationAvatar(Struct_Event_Conversation_ChangeConversationAvatar $event_data): void
	{

		// проходимся по всем участникам диалога
		foreach ($event_data->users as $k => $_) {

			// обновляем название группового диалога в записях пользователя
			Type_Conversation_LeftMenu::onChangeAvatar(
				$k,
				$event_data->conversation_map,
				$event_data->avatar_file_map
			);
		}
	}

	/**
	 * Callback для события добавления нового сообщения в диалог.
	 */
	#[Type_Attribute_EventListener(Type_Event_Conversation_AddMessage::EVENT_TYPE)]
	public static function onMessageAdd(Struct_Event_Conversation_AddMessage $event_data): void
	{

		try {

			Helper_Conversations::addMessage(
				$event_data->conversation_map,
				$event_data->message,
				$event_data->users,
				$event_data->conversation_type,
				$event_data->conversation_name,
				$event_data->conversation_extra
			);
		} catch (cs_ConversationIsLocked) {

		}
	}

	/**
	 * Callback для события создания сингл-диалогов для пользователя.
	 */
	#[Type_Attribute_EventListener(Type_Event_Conversation_AddSingleList::EVENT_TYPE)]
	public static function addSingleList(Struct_Event_Conversation_AddSingleList $event_data): void
	{

		foreach ($event_data->opponent_user_id_list as $opponent_user_id) {

			try {
				Helper_Single::createIfNotExist($event_data->user_id, $opponent_user_id, $event_data->is_hidden_for_user, $event_data->is_hidden_for_opponent);
			} catch (\Exception $e) {

				// трейс для удобства
				$trace_message = var_export($e->getTraceAsString(), true);

				// стараемся не умирать без причины
				// сохраняем сообщение об ошибке
				$exception_message = $e->getMessage() ?: "empty exception message";
				$exception_message = "{$exception_message}\ntrace\n$trace_message";

				Type_System_Admin::log(
					"event-add-single-list-exception",
					"error occurred during single creation between {$event_data->user_id} and {$opponent_user_id}: {$exception_message}"
				);
			}
		}
	}

	/**
	 * Callback для события обновления ласт_месседж при изменении сообщения диалога.
	 */
	#[Type_Attribute_EventListener(Type_Event_Conversation_UpdateLastMessageOnMessageUpdate::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onUpdateLastMessageOnMessageUpdate(Struct_Event_Conversation_UpdateLastMessageOnMessageUpdate $event_data): void
	{

		foreach ($event_data->users as $k => $_) {
			self::_updateLeftMenuOnMessageUpdate($k, $event_data->conversation_map, $event_data->message);
		}
	}

	// обновляем last_message в left_menu при обновлении сообщения
	protected static function _updateLeftMenuOnMessageUpdate(int $user_id, string $conversation_map, array $message): bool
	{

		// получаем запись из левого меню
		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getOne($user_id, $conversation_map);
		if (!isset($left_menu_row["user_id"])) {
			return false;
		}

		// проверяем, быть может пользователь покинул диалог
		if ($left_menu_row["is_leaved"] == 1) {
			return true;
		}

		// получаем индексы сообщений, и обновляем запись левого меню если нужно
		$current_message_conversation_message_index = self::_getCurrentMessageConversationIndex($message);
		$last_message_conversation_message_index    = self::_getLastMessageConversationIndex($left_menu_row["last_message"]);
		if (self::_isNeedUpdateLeftMenuOnMessageUpdate($current_message_conversation_message_index, $last_message_conversation_message_index)) {

			$last_message = self::_makeLastMessage($message, $user_id);
			$set          = ["last_message" => $last_message];

			if (Type_Conversation_Message_Main::getHandler($message)::isUserMention($message, $user_id)) {
				$set["is_mentioned"] = 1;
			}

			Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
		}

		return true;
	}

	/**
	 * Пользователь присоединился к диалогу.
	 *
	 * @throws \busException
	 * @throws \paramException
	 */
	#[Type_Attribute_EventListener(Type_Event_UserConversation_UserJoinedConversation::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onUserJoinedConversation(Struct_Event_UserConversation_UserJoinedConversation $event_data): void
	{

		// получаем данные для диалога
		$conversation_meta = Type_Conversation_Meta::get($event_data->conversation_map);

		// название диалога
		$opponent_user_id  = Type_Conversation_Meta_Users::getOpponentId($event_data->user_id, $conversation_meta["users"]) ?? 0;
		$conversation_name = $conversation_meta["conversation_name"];

		// если это сингл, то нужно получить имя собеседника
		if (Type_Conversation_Meta::isSubtypeOfSingle((int)$conversation_meta["type"]) && $opponent_user_id !== 0) {

			// получаем данные о пользователе
			$user_info         = Gateway_Bus_CompanyCache::getMember($opponent_user_id);
			$conversation_name = $user_info->full_name;
		}

		// обновляем левое меню пользователя
		self::_updateLeftMenu($event_data, $conversation_name);
	}

	/**
	 * обновляем левое меню пользователя и поисковый индекс по названию диалога
	 *
	 * @long большие структуры
	 */
	protected static function _updateLeftMenu(Struct_Event_UserConversation_UserJoinedConversation $event_data, string $conversation_name): void
	{

		$last_message = self::_getLastMessageFromConversation($event_data);

		$set["conversation_name"] = $conversation_name;
		if ($last_message != false) {
			$set["last_message"] = $last_message;
		}
		Gateway_Db_CompanyConversation_UserLeftMenu::set($event_data->user_id, $event_data->conversation_map, $set);
	}

	// получаем последнее сообщение из диалога
	protected static function _getLastMessageFromConversation(Struct_Event_UserConversation_UserJoinedConversation $event_data): bool | array
	{

		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($event_data->conversation_map);
		if ($dynamic_row["last_block_id"] < 1) {
			return false;
		}

		$block_row = Domain_Conversation_Entity_Message_Block_Get::getBlockListRowByIdList(
			$event_data->conversation_map,
			[$dynamic_row["last_block_id"]]
		)[$dynamic_row["last_block_id"]];
		$last_message_map = Type_Conversation_Message_Block::getLastMessageMap($block_row["data"], true);
		if ($last_message_map == "") {
			return false;
		}

		$message = Domain_Conversation_Entity_Message_Block_Message::get($last_message_map, $block_row);

		// если пользователь не может просматривать сообщение (например скрыл его)
		if (Type_Conversation_Message_Main::getHandler($message)::isMessageHiddenForUser($message, $event_data->user_id)) {
			return false;
		}

		$clear_until = Domain_Conversation_Entity_Dynamic::getClearUntil($dynamic_row["user_clear_info"], $dynamic_row["conversation_clear_info"], $event_data->user_id);

		// если дата создания сообщения раньше, чем отметка до которой пользователь очистил диалог
		if (Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message) < $clear_until) {
			return false;
		}
		return Type_Conversation_LeftMenu::makeLastMessage($message);
	}

	/**
	 * Нужно распарсить ссылку и добавить preview.
	 *
	 * @throws Domain_Conversation_Exception_Preview_IncorrectUrl
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	#[Type_Attribute_EventListener(Type_Event_Conversation_LinkParseRequired::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onLinkParseRequired(Struct_Event_Conversation_LinkParseRequired $event_data): void
	{

		$worker = new Type_Preview_Worker();
		$worker->doWork(
			$event_data->message_map,
			$event_data->user_id,
			$event_data->link_list,
			$event_data->lang,
			$event_data->user_list,
			$event_data->entity_info,
			$event_data->need_full_preview,
		);
	}

	/**
	 * Начат процесс покидания диалога.
	 *
	 * @throws \parseException|\paramException
	 * @long switch-case
	 */
	public static function onLeaveConversationInitiated(Struct_Event_UserConversation_LeaveConversationInitiated $event_data): void
	{

		$left_menu_row = Type_Conversation_LeftMenu::get($event_data->user_id, $event_data->conversation_map);

		// обрабатываем случаи сингл и груп диалогов
		switch ($left_menu_row["type"]) {

			// случай для single диалога
			case CONVERSATION_TYPE_SINGLE_DEFAULT:
			case CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT:

				Domain_Conversation_Action_DoLeaveSingle::do($event_data->conversation_map, $event_data->user_id, $left_menu_row["opponent_user_id"]);
				break;

				// случай для group дилога
			case CONVERSATION_TYPE_GROUP_DEFAULT:
			case CONVERSATION_TYPE_GROUP_GENERAL:

				$meta_row = Type_Conversation_Meta::get($event_data->conversation_map);

				// нужно ли системное сообщение о том, что пользователь покинул компанию
				$is_need_system_message_about_company_left = Type_Conversation_Meta_Extra::isNeedSystemMessageOnDismissal($meta_row["extra"]);

				// получаем флаг нужно ли системное сообщение о том, что пользователь покинул группу в зависимости от другого флага
				$is_need_system_message_about_group_left = !$is_need_system_message_about_company_left;

				// покидаем групповой диалог
				Helper_Groups::doLeave(
					$event_data->conversation_map,
					$event_data->user_id,
					$meta_row,
					$is_need_system_message_about_group_left,
					false,
					$is_need_system_message_about_company_left
				);
				break;

				// случай для hiring дилога
			case CONVERSATION_TYPE_GROUP_HIRING:

				// покидаем групповой диалог
				$meta_row = Type_Conversation_Meta::get($event_data->conversation_map);
				Helper_Groups::doLeave($event_data->conversation_map, $event_data->user_id, $meta_row, false);
				break;

			default:
				throw new ParseFatalException(__METHOD__ . ": undefined conversation type");
		}
	}

	/**
	 * Требуется создать дефолтные группы
	 *
	 * @throws \parseException
	 */
	#[Type_Attribute_EventListener(Type_Event_Company_ExtendedEmployeeCardEnabled::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onExtendedEmployeeCardEnabled(Struct_Event_Company_ExtendedEmployeeCardEnabled $event_data): void
	{

		Domain_Group_Action_CompanyExtendedCardJoin::do($event_data->user_id_list, $event_data->creator_user_id);
	}

	// получаем conversation_message_index текущего сообщения
	protected static function _getCurrentMessageConversationIndex(array $message): int
	{

		$message_map = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);

		return \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($message_map);
	}

	// получаем conversation_message_index последнего сообщения
	protected static function _getLastMessageConversationIndex(array $last_message): int
	{

		return Gateway_Db_CompanyConversation_UserLeftMenu::getConversationMessageIndex($last_message);
	}

	// нужно ли обновлять левое меню при обновлении сообщения (изменении текста сообщения, удалении/скрытии сообщения)
	protected static function _isNeedUpdateLeftMenuOnMessageUpdate(int $current_message_conversation_message_index, int $last_message_conversation_message_index): bool
	{

		// если conversation_message_index равен нулю, значит last_message пуст
		if ($last_message_conversation_message_index == 0) {
			return false;
		}

		// если conversation_message_index обновленного сообщения меньше того, что записан в last_message
		if ($current_message_conversation_message_index < $last_message_conversation_message_index) {
			return false;
		}

		return true;
	}

	// формируем объект last_message
	// @long - большая структура для формирования сообщения
	protected static function _makeLastMessage(array $message, int $user_id): array
	{

		$last_message = Type_Conversation_LeftMenu::makeLastMessage($message);

		// если сообщение скрыто
		if (Type_Conversation_Message_Main::getHandler($message)::isMessageHiddenForUser($message, $user_id)) {
			$last_message = [];
		}

		return $last_message;
	}

	/**
	 * Процесс очистки диалога у пользователей
	 *
	 * @throws \parseException
	 */
	#[Type_Attribute_EventListener(Type_Event_Conversation_ClearConversationForUsers::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onClearConversationForUsers(Struct_Event_Conversation_ClearConversationForUsers $event_data): void
	{

		// пересчитываем total_unread_count для каждого пользователя из списка
		foreach ($event_data->user_id_list as $user_id) {
			Type_Conversation_LeftMenu::recountTotalUnread($user_id);
		}

		// обновляем badge с непрочитанными для списка пользователей
		$extra = Gateway_Bus_Company_Timer::getExtraForUserIdListUpdateBadge($event_data->user_id_list, [$event_data->conversation_map], true);

		Gateway_Bus_Company_Timer::setTimeoutForUserIdList(Gateway_Bus_Company_Timer::UPDATE_BADGE, generateRandomString(), [], $extra);

		// отправляем событие каждому пользователю из списка
		Gateway_Bus_Sender::conversationClearMessages($event_data->user_id_list, $event_data->conversation_map, $event_data->messages_updated_version);
	}

	/**
	 * Добавляем пользователя в список групп
	 *
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \parseException
	 */
	#[Type_Attribute_EventListener(Type_Event_Conversation_JoinToGroupList::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onJoinToGroupList(Struct_Event_Conversation_JoinToGroupList $event_data): void
	{

		Domain_Conversation_Action_JoinToGroupList::do($event_data->user_id, $event_data->conversation_map_list);
	}

	/**
	 * Процесс асинхронной очистки диалогов из source-скрипта
	 *
	 * @throws ReturnFatalException|ParseFatalException
	 * @long
	 */
	#[Type_Attribute_EventListener(Type_Event_Conversation_AsyncSourceClearConversations::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onAsyncSourceClearConversations(Struct_Event_Conversation_AsyncSourceClearConversations $event_data): void
	{

		$conversation_map_list = $event_data->conversation_map_list;
		if (count($conversation_map_list) < 1) {
			return;
		}

		// делим полученный список чатов на части:
		// рабочий список, который обрабатываем в ивенте, и список для следующей итерации
		$work_conversation_map_list       = array_slice($conversation_map_list, 0, 30);
		$next_cycle_conversation_map_list = array_slice($conversation_map_list, 30);

		$clear_until = $event_data->clear_until;

		$meta_row_list = Type_Conversation_Meta::getAll($work_conversation_map_list, true);

		// проходимся по диалогам компании
		$all_user_id_list = [];
		foreach ($work_conversation_map_list as $conversation_map) {

			Type_Conversation_Meta::setConversationClearUntilForAll($conversation_map, $clear_until);

			if (!isset($meta_row_list[$conversation_map])) {
				continue;
			}
			$meta_row = $meta_row_list[$conversation_map];

			// получаем пользователей состоящих в диалоге
			$user_id_list = [];
			foreach ($meta_row["users"] as $member_user_id => $_) {

				if (Type_Conversation_Meta_Users::isMember($member_user_id, $meta_row["users"])) {

					$user_id_list[]   = $member_user_id;
					$all_user_id_list = array_merge($all_user_id_list, $user_id_list);
				}
			}

			// устанавливаем clear_until в left_menu
			Type_Conversation_LeftMenu::setClearUntilForUserIdList($user_id_list, $conversation_map, $clear_until);

			// обновляем время очистки диалога для всех пользователей
			Domain_Conversation_Entity_Dynamic::setClearUntilConversationForUserIdList($conversation_map, $user_id_list, $clear_until, true);

			// делаем сокет запрос в модуль php_thread для обновления времени очистки диалога
			Gateway_Socket_Thread::clearConversationForUserIdList($conversation_map, $clear_until, $user_id_list);

			// обновляем badge с непрочитанными для списка пользователей
			$extra = Gateway_Bus_Company_Timer::getExtraForUserIdListUpdateBadge($user_id_list, [$conversation_map], true);
			Gateway_Bus_Company_Timer::setTimeoutForUserIdList(Gateway_Bus_Company_Timer::UPDATE_BADGE, generateRandomString(), [], $extra);
		}

		// пересчитываем total_unread_count для каждого пользователя из списка
		foreach (array_unique($all_user_id_list) as $user_id) {
			Type_Conversation_LeftMenu::recountTotalUnread($user_id);
		}

		Domain_Search_Entity_Conversation_Task_Reindex::queueList($work_conversation_map_list);

		// отправляем оставшийся список чатов на следующую итерацию
		count($next_cycle_conversation_map_list) > 0 && Gateway_Event_Dispatcher::dispatch(
			Type_Event_Conversation_AsyncSourceClearConversations::create($next_cycle_conversation_map_list, $clear_until)
		);
	}
}
