<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для исполнения задач через phphooker
 * ВАЖНО! для каждого типа задачи должна быть функция здесь
 */
class Type_Phphooker_Main {

	##########################################################
	# region типы задач
	##########################################################

	public const TASK_TYPE_CHANGE_CONVERSATION_NAME                                = 3; // обновление conversation_name группового диалога
	public const TASK_TYPE_CHANGE_CONVERSATION_AVATAR                              = 4; // обновление avatar_file_map группового диалога
	public const TASK_TYPE_UPDATE_USER_DATA_ON_MESSAGE_ADD                         = 6; // обновляем таблицы пользователя при добавлении сообщения в диалог
	public const TASK_TYPE_ADD_MESSAGE_LIST                                        = 7; // добавляем и отправляем сообщения в диалог
	public const TASK_TYPE_UPDATE_LAST_MESSAGE_ON_MESSAGE_UPDATE                   = 8; // обновляем last_message пользователей при обновлении последнего сообщения
	public const TASK_TYPE_UPDATE_MEMBER_COUNT                                     = 9; // обновляем member_count для left_menu при изменении количества юзеров в группе
	public const TASK_TYPE_UPDATE_LAST_MESSAGE_ON_SET_MESSAGE_AS_LAST              = 11; // обновляем last_message пользователя при установке сообщения последним
	public const TASK_TYPE_UPDATE_LAST_MESSAGE_ON_MESSAGE_UPDATE_FOR_SYSTEM_DELETE = 12; // обновляем last_message пользователя при скрытии последнего сообщения
	public const TASK_TYPE_SET_DELETED_REPOST_REL                                  = 14; // помечаем удаленной запись с историей о репосте
	public const TASK_TYPE_SET_DELETED_THREAD_REPOST_REL                           = 15; // помечаем удаленной запись с историей о репосте из треда
	public const TASK_TYPE_JOIN_TO_GROUP                                           = 16; // вступаем в группу
	public const TASK_TYPE_SET_PARENT_MESSAGE_IS_DELETED                           = 17; // помечаем что родительское сообщение удалено
	public const TASK_TYPE_UPDATE_CONVERSATION_AFTER_UNBLOCK                       = 18; // обновляем диалоги
	public const TASK_TYPE_SEND_INVITE_TO_USER                                     = 19; // отправляем сообщение с приглашением
	public const TASK_TYPE_UPDATE_ALLOW_STATUS_ALIAS_IN_LEFT_MENU                  = 20; // обновляем поле allow_status_alias в левом меню
	public const TASK_TYPE_SET_INACTIVE_INVITE                                     = 21; // помечаем инвайты неактивными
	public const TASK_TYPE_SET_DECLINE_INVITE                                      = 22; // помечаем инвайты отклонеными
	public const TASK_TYPE_SET_SPHINX_DELETED_USER_ON_LEAVE_GROUP                  = 23; // помечаем пользователя удаленным в sphinx при покидании того группы
	public const TASK_TYPE_CHANGE_USER_ROLE_IN_GROUP                               = 24; // меняем роль пользователя в группе
	public const TASK_TYPE_DO_UNFOLLOW_THREAD_LIST                                 = 25; // отписываем от тредов
	public const TASK_TYPE_SET_PARENT_MESSAGE_LIST_IS_DELETED                      = 26; // помечаем что родительское сообщение удалено
	public const TASK_TYPE_UPDATE_LEFT_MENU_FOR_USER_ON_MESSAGE_EDIT               = 27; // обновляем левое меню при редактировании сообщения
	public const TASK_TYPE_UPDATE_PROFILE_DATA_TO_SPHINX_GROUP_MEMBER              = 28; // обновляем пользовательские данные в сфинксе для групп, где тот числится участником
	public const TASK_TYPE_TRY_UNFOLLOW_THREAD_BY_CONVERSATION_MAP                 = 29; // пробуем отписать пользователя от всех тредов после покидания группы
	public const TASK_TYPE_CHANGE_GROUP_BASE_INFO                                  = 31; // обновление основной информации группового диалога (название, описание, аватарка)
	public const TASK_TYPE_CHANGE_HIDDEN_THREAD_ON_HIDDEN_CONVERSATION             = 32; // скрывать/открывать тред, если скрыл сообщение в диалоге
	public const TASK_TYPE_CLEAR_THREAD_META_CACHE                                 = 33;
	public const TASK_TYPE_CLEAR_PARENT_MESSAGE_CACHE                              = 34;
	public const TASK_TYPE_CLEAR_PARENT_MESSAGE_LIST_CACHE                         = 35;
	public const TASK_TYPE_UPDATE_LAST_MESSAGE_ON_DELETE_IF_DISABLED_SHOW_MESSAGE  = 36; // обновляем last_message пользователей при удалении и отключенном показе удаленных сообщений
	public const TASK_TYPE_CHANGE_SYSTEM_DELETED_MESSAGE_CONVERSATION              = 37; // отключен показ системных сообщений об удаленных сообщениях
	public const TASK_TYPE_CHANGE_CHANNEL_OPTION                                   = 38; // сменили опцию канала в группе
	public const TASK_TYPE_GRANT_ADMIN_RIGHTS_FOR_CONVERSATION_LIST                = 39; // делаем пользователя админом в группах в которых необходимо

	# endregion
	##########################################################

	##########################################################
	# region методы для добавления задачи
	##########################################################

	// событие при обновлении названия группового диалога
	public static function onChangeConversationName(string $conversation_map, array $users, string $conversation_name):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_CHANGE_CONVERSATION_NAME, [
			"conversation_map"  => $conversation_map,
			"users"             => $users,
			"conversation_name" => $conversation_name,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// событие при обновлении аватара группового диалога
	public static function onChangeConversationAvatar(string $conversation_map, array $users, string $avatar_file_map):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_CHANGE_CONVERSATION_AVATAR, [
			"conversation_map" => $conversation_map,
			"users"            => $users,
			"avatar_file_map"  => $avatar_file_map,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	/**
	 * событие при обновлении основной информации группы
	 *
	 * @param string       $conversation_map
	 * @param array        $users
	 * @param string|false $group_name
	 * @param string|false $avatar_file_map
	 *
	 * @throws ParseFatalException
	 * @mixed - $group_name, $group_description, $avatar_file_map могут быть false
	 */
	public static function onChangeGroupBasicInfo(string $conversation_map, array $users, string|false $group_name, string|false $avatar_file_map):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_CHANGE_GROUP_BASE_INFO, [
			"conversation_map" => $conversation_map,
			"users"            => $users,
			"group_name"       => $group_name,
			"avatar_file_map"  => $avatar_file_map,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// обновляем таблицы пользователя при добавлении сообщения в диалог
	public static function updateUserDataOnMessageAdd(string $conversation_map, array $message, array $users, int $messages_count):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_UPDATE_USER_DATA_ON_MESSAGE_ADD, [
			"conversation_map" => $conversation_map,
			"users"            => $users,
			"message"          => $message,
			"messages_count"   => $messages_count,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// отправляем сообщение
	public static function addMessage(string $conversation_map, array $message, array $users, int $conversation_type, string $conversation_name, array $conversation_extra, bool $is_silent = true):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_ADD_MESSAGE_LIST, [
			"conversation_map"   => $conversation_map,
			"message_list"       => [$message],
			"users"              => $users,
			"conversation_type"  => $conversation_type,
			"conversation_name"  => $conversation_name,
			"conversation_extra" => $conversation_extra,
			"is_silent"          => $is_silent,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// отправляем список сообщений за раз
	public static function addMessageList(string $conversation_map, array $message_list, array $users, int $conversation_type, string $conversation_name, array $conversation_extra, bool $is_silent = true):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_ADD_MESSAGE_LIST, [
			"conversation_map"   => $conversation_map,
			"message_list"       => $message_list,
			"users"              => $users,
			"conversation_type"  => $conversation_type,
			"conversation_name"  => $conversation_name,
			"conversation_extra" => $conversation_extra,
			"is_silent"          => $is_silent,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// обновляем last_message пользователей при обновлении последнего сообщения
	public static function updateLastMessageOnMessageUpdate(string $conversation_map, array $message, array $users):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_UPDATE_LAST_MESSAGE_ON_MESSAGE_UPDATE, [
			"conversation_map" => $conversation_map,
			"message"          => $message,
			"users"            => $users,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// обновляем left_menu пользователей при редактировании сообщения
	public static function updateLeftMenuForUserOnMessageEdit(string $conversation_map, array $message, array $users, array $new_mentioned_user_id_list):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_UPDATE_LEFT_MENU_FOR_USER_ON_MESSAGE_EDIT, [
			"conversation_map"           => $conversation_map,
			"message"                    => $message,
			"users"                      => $users,
			"new_mentioned_user_id_list" => $new_mentioned_user_id_list,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// обновляем last_message пользователей при системном удалении сообщения
	public static function updateLastMessageOnMessageUpdateForSystemDelete(string $conversation_map, array $message, array $users):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_UPDATE_LAST_MESSAGE_ON_MESSAGE_UPDATE_FOR_SYSTEM_DELETE, [
			"conversation_map" => $conversation_map,
			"message"          => $message,
			"users"            => $users,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// обновляем last_message пользователя при установке сообщения последним
	public static function updateLastMessageOnSetMessageAsLast(string $conversation_map, array $message, int $user_id):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_UPDATE_LAST_MESSAGE_ON_SET_MESSAGE_AS_LAST, [
			"conversation_map" => $conversation_map,
			"message"          => $message,
			"user_id"          => $user_id,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// обновляем member_count для left_menu при изменении количества юзеров в группе
	public static function updateMembersCount(string $conversation_map, array $users):void {

		// добавляем задачу
		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_UPDATE_MEMBER_COUNT, [
			"conversation_map" => $conversation_map,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// помечаем удаленной запись с историей о репосте
	// - $conversation_map - диалог донор
	// - $message_map - удаленное сообщение с репостом
	public static function setRepostRelMessageDeleted(string $conversation_map, string $message_map):void {

		// добавляем задачу
		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_SET_DELETED_REPOST_REL, [
			"conversation_map" => $conversation_map,
			"message_map"      => $message_map,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// помечаем удаленной запись с историей о репосте в базе данных модуля php_thread
	// - $thread_map - тред откуда репост
	// - $message_map - удаленное сообщение с репостом
	public static function setThreadRepostRelMessageDeleted(string $thread_map, string $message_map):void {

		// добавляем задачу
		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_SET_DELETED_THREAD_REPOST_REL, [
			"thread_map"  => $thread_map,
			"message_map" => $message_map,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// отправляем socket запрос на php_thread, чтобы пометить, что родительское сообщение удалено
	public static function setParentMessageIsDeletedIfThreadExist(string $thread_map):void {

		// добавляем задачу
		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_SET_PARENT_MESSAGE_IS_DELETED, [
			"thread_map" => $thread_map,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// отправляем socket запрос на php_thread, чтобы пометить, что родительское сообщение удалено
	public static function setParentMessageListIsDeletedIfThreadExist(array $thread_map_list):void {

		// добавляем задачу
		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_SET_PARENT_MESSAGE_LIST_IS_DELETED, [
			"thread_map_list" => $thread_map_list,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// обновляем диалоги разблокированного пользователя
	public static function updateConversationAfterUnblock(string $conversation_map, int $user_id, int $opponent_user_id, int $type):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_UPDATE_CONVERSATION_AFTER_UNBLOCK, [
			"conversation_map" => $conversation_map,
			"user_id"          => $user_id,
			"opponent_user_id" => $opponent_user_id,
			"type"             => $type,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// отправляем сообщение с приглашением пользователю
	public static function sendInviteToUser(int $inviter_user_id, int $invited_user_id, string $invite_map, string $single_conversation_map, array $group_meta_row, bool $is_need_send_system_message):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_SEND_INVITE_TO_USER, [
			"invited_user_id"             => $invited_user_id,
			"inviter_user_id"             => $inviter_user_id,
			"invite_map"                  => $invite_map,
			"group_meta_row"              => $group_meta_row,
			"single_conversation_map"     => $single_conversation_map,
			"is_need_send_system_message" => $is_need_send_system_message,
			"platform"                    => Type_Api_Platform::getPlatform(),
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// обновляем allow_status_alias в левом меню
	public static function updateAllowStatusAliasInLeftMenu(int $allow_status, array $extra, string $conversation_map, int $user_id, int $opponent_user_id):void {

		// отправляем задачу для пользователя вызвашего событие
		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_UPDATE_ALLOW_STATUS_ALIAS_IN_LEFT_MENU, [
			"allow_status"     => $allow_status,
			"extra"            => $extra,
			"conversation_map" => $conversation_map,
			"user_id"          => $user_id,
			"opponent_user_id" => $opponent_user_id,

		]);

		Gateway_Event_Dispatcher::dispatch($event_data);

		// отправляем задачу для собеседника пользователя
		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_UPDATE_ALLOW_STATUS_ALIAS_IN_LEFT_MENU, [
			"allow_status"     => $allow_status,
			"extra"            => $extra,
			"conversation_map" => $conversation_map,
			"user_id"          => $opponent_user_id,
			"opponent_user_id" => $user_id,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// помечаем неактивный инвайты
	public static function setInactiveAllUserInviteToConversation(int $user_id, string $conversation_map, int $inactive_reason, bool $is_remove_user = false):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_SET_INACTIVE_INVITE, [
			"user_id"          => $user_id,
			"conversation_map" => $conversation_map,
			"inactive_reason"  => $inactive_reason,
			"is_remove_user"   => $is_remove_user,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// помечаем отклоненными инвайты
	public static function setDeclineAllUserInviteToConversation(int $user_id, string $conversation_map):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_SET_DECLINE_INVITE, [
			"user_id"          => $user_id,
			"conversation_map" => $conversation_map,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// помечаем пользователя удаленным в sphinx при покидании того группы
	public static function setSphinxDeletedUserOnLeaveGroup(int $user_id, string $conversation_map):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_SET_SPHINX_DELETED_USER_ON_LEAVE_GROUP, [
			"conversation_map" => $conversation_map,
			"user_id"          => $user_id,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// меняем роль пользователя в групповом диалоге
	public static function changeUserRoleInGroup(string $conversation_map, int $user_id, int $role):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_CHANGE_USER_ROLE_IN_GROUP, [
			"conversation_map" => $conversation_map,
			"user_id"          => $user_id,
			"role"             => $role,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// отписываем пользователя от тредов
	public static function doUnfollowThreadList(int $user_id, array $thread_map_list):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_DO_UNFOLLOW_THREAD_LIST, [
			"user_id"         => $user_id,
			"thread_map_list" => $thread_map_list,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// скрываем тред пользователя при скрытии сообщения треда
	public static function doHideParentThreadOnHideConversation(int $user_id, array $thread_map_list, bool $need_to_hide_parent_thread):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_CHANGE_HIDDEN_THREAD_ON_HIDDEN_CONVERSATION, [
			"user_id"                    => $user_id,
			"thread_map_list"            => $thread_map_list,
			"need_to_hide_parent_thread" => $need_to_hide_parent_thread,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// обновляем данные пользователя в сфинксе для групп, где тот числится участником
	public static function updateProfileDataToSphinxGroupMember(int $user_id, array $conversation_map_list):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_UPDATE_PROFILE_DATA_TO_SPHINX_GROUP_MEMBER, [
			"user_id"               => $user_id,
			"conversation_map_list" => $conversation_map_list,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// отписываем пользователя от тредов
	public static function doUnfollowThreadListByConversationMap(int $user_id, string $conversation_map):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_TRY_UNFOLLOW_THREAD_BY_CONVERSATION_MAP, [
			"user_id"          => $user_id,
			"conversation_map" => $conversation_map,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// функция для отправки задачи на очистку кэша thread_meta
	public static function sendClearThreadMetaCache(string $conversation_map):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_CLEAR_THREAD_META_CACHE, [
			"source_parent_map" => $conversation_map,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// функция для отправки задачи на очистку кэша родительского сообщения треда
	public static function sendClearParentMessageCache(string $message_map):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_CLEAR_PARENT_MESSAGE_CACHE, [
			"message_map" => $message_map,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// функция для отправки задачи на очистку кэша родительских сообщений тредов
	public static function sendClearParentMessageListCache(array $message_map_list):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_CLEAR_PARENT_MESSAGE_LIST_CACHE, [
			"message_map_list" => $message_map_list,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// обновляем последнее сообщение пользователей при удалении если отключен показ удаленных сообщений
	public static function updateLastMessageOnDeleteIfDisabledShowDeleteMessage(string $conversation_map, array $message, array $users):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_UPDATE_LAST_MESSAGE_ON_DELETE_IF_DISABLED_SHOW_MESSAGE, [
			"conversation_map" => $conversation_map,
			"message"          => $message,
			"users"            => $users,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// отключен показ системных сообщений об удаленных сообщениях
	public static function doChangeSystemDeletedMessageConversation(string $conversation_map, bool $value, array $users):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_CHANGE_SYSTEM_DELETED_MESSAGE_CONVERSATION, [
			"conversation_map" => $conversation_map,
			"value"            => $value,
			"users"            => $users,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// сменили опцию канала в группе
	public static function doChangeChannelOption(string $conversation_map, int $is_channel, array $users):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_CHANGE_CHANNEL_OPTION, [
			"conversation_map" => $conversation_map,
			"is_channel"       => $is_channel,
			"users"            => $users,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// делаем пользователя админом в группах в которых необходимо
	public static function grantAdminRightsForConversationList(int $user_id, int $user_role, int $user_permissions, array $conversation_map_list):void {

		$event_data = Type_Event_Task_TaskAddedConversation::create(self::TASK_TYPE_GRANT_ADMIN_RIGHTS_FOR_CONVERSATION_LIST, [
			"user_id"               => $user_id,
			"user_role"             => $user_role,
			"user_permissions"      => $user_permissions,
			"conversation_map_list" => $conversation_map_list,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	# endregion
	##########################################################
}