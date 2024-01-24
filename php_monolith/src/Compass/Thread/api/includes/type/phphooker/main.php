<?php

namespace Compass\Thread;

/**
 * класс для исполнения задач через phphooker
 * attention! для каждого типа задачи должна быть функция здесь
 */
class Type_Phphooker_Main {

	##########################################################
	# region типы задач
	##########################################################

	public const TASK_TYPE_UPDATE_USER_DATA_ON_MESSAGE_ADD                  = 3; // обновление пользовательских данных при добавлении сообщения в тред
	public const TASK_TYPE_UNFOLLOW_THREAD_LIST                             = 5; // отписываем пользователя от списка тредов
	public const TASK_TYPE_UPDATE_USER_DATA_FOR_MENTIONED_ON_MESSAGE_EDIT   = 6; // обновляем пользовательские данные при редактировании сообщения
	public const TASK_TYPE_CLEAR_FOLLOW_USER                                = 7; // актуализируем список follow для пользователя
	public const TASK_TYPE_UPDATE_USER_DATA_FOR_MENTIONED_ON_MESSAGE_DELETE = 8; // обновляем пользовательские данные упомянутых при удалении сообщения
	public const TASK_TYPE_UPDATE_USER_DATA_FOR_MENTIONED_ON_MESSAGE_HIDE   = 9; // обновляем пользовательские данные упомянутого при скрытии сообщения
	public const TASK_TYPE_UPDATE_USER_INBOX_FOR_UNREAD_LESS_ZERO           = 10; // !!! - обновляем пользовательские данные, если количество непрочитанных меньше нуля
	public const TASK_TYPE_UPDATE_USER_THREAD_MENU_FOR_UNREAD_LESS_ZERO     = 11; // !!! - обновляем пользовательское меню тредов, если количество непрочитанных меньше нуля

	# endregion
	##########################################################

	##########################################################
	# region методы для добавления задач
	##########################################################

	// обновить пользовательские данные в базе данных пользовательской таблице при отправке сообщения в тред
	public static function updateUserDataOnMessageAdd(string $thread_map, array $message, array $user_list):void {

		$event_data = Type_Event_Task_TaskAddedThread::create(self::TASK_TYPE_UPDATE_USER_DATA_ON_MESSAGE_ADD, [
			"thread_map" => $thread_map,
			"user_list"  => $user_list,
			"message"    => $message,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// отписываем пользователя от списка тредов
	public static function doUnfollowThreadList(array $thread_map_list, int $user_id):void {

		if (count($thread_map_list) < 1) {
			return;
		}

		$event_data = Type_Event_Task_TaskAddedThread::create(self::TASK_TYPE_UNFOLLOW_THREAD_LIST, [
			"thread_map_list" => $thread_map_list,
			"user_id"         => $user_id,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// обновляем пользовательские данные при редактировании сообщения
	public static function updateUserDataForMentionedOnMessageEdit(string $thread_map, array $new_mentioned_user_id_list, array $remove_mentioned_user_id_list):void {

		$event_data = Type_Event_Task_TaskAddedThread::create(self::TASK_TYPE_UPDATE_USER_DATA_FOR_MENTIONED_ON_MESSAGE_EDIT, [
			"thread_map"                 => $thread_map,
			"new_mentioned_user_list"    => $new_mentioned_user_id_list,
			"remove_mentioned_user_list" => $remove_mentioned_user_id_list,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// обновляем пользовательские данные упомянутых при удалении сообщения
	public static function updateUserDataForMentionedOnMessageDelete(string $thread_map, array $mentioned_user_id_list):void {

		$event_data = Type_Event_Task_TaskAddedThread::create(self::TASK_TYPE_UPDATE_USER_DATA_FOR_MENTIONED_ON_MESSAGE_DELETE, [
			"thread_map"   => $thread_map,
			"user_id_list" => $mentioned_user_id_list,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// обновляем пользовательские данные упомянутого при скрытии сообщения
	public static function updateUserDataForMentionedOnMessageHide(string $thread_map, array $mentioned_user_id_list):void {

		$event_data = Type_Event_Task_TaskAddedThread::create(self::TASK_TYPE_UPDATE_USER_DATA_FOR_MENTIONED_ON_MESSAGE_HIDE, [
			"thread_map"   => $thread_map,
			"user_id_list" => $mentioned_user_id_list,
		]);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	/**
	 * обновляем данные пользователя для актуализации непрочитанных
	 *
	 * @throws \parseException
	 */
	public static function updateUserInboxForUnreadLessZero(int $user_id):void {

		$params = [
			"user_id" => $user_id,
		];

		$event_data = Type_Event_Task_TaskAddedThread::create(self::TASK_TYPE_UPDATE_USER_INBOX_FOR_UNREAD_LESS_ZERO, $params);
		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	/**
	 * обновляем данные пользовательского меню, если количество непрочитанных меньше нуля
	 *
	 * @throws \parseException
	 */
	public static function updateUserThreadMenuForUnreadLessZero(int $user_id, array $thread_map_list):void {

		$params = [
			"user_id"         => $user_id,
			"thread_map_list" => $thread_map_list,
		];

		$event_data = Type_Event_Task_TaskAddedThread::create(self::TASK_TYPE_UPDATE_USER_THREAD_MENU_FOR_UNREAD_LESS_ZERO, $params);
		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	# endregion
	##########################################################
}