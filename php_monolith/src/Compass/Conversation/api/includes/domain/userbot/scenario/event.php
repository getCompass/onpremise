<?php declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Класс обработки сценариев событий.
 */
class Domain_Userbot_Scenario_Event {

	/**
	 * событие при включении бота
	 */
	#[Type_Attribute_EventListener(Type_Event_Userbot_Enabled::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onUserbotEnabled(Struct_Event_Userbot_Enabled $event_data):void {

		$userbot_user_id       = $event_data->userbot_user_id;
		$userbot_id            = $event_data->userbot_id;
		$conversation_map_list = $event_data->conversation_map_list;
		$user_id_list          = $event_data->user_id_list;

		// получаем меты диалогов
		$meta_list = Type_Conversation_Meta::getAll($conversation_map_list);

		// собираем всех пользователей
		$all_user_id_list = $user_id_list;

		// получаем участников диалогов
		foreach ($meta_list as $meta) {

			$meta_user_id_list = array_keys($meta["users"]);
			$all_user_id_list  = array_merge($all_user_id_list, $meta_user_id_list);
		}

		// обновляем флаг is_allowed для сингл-диалогов с ботом
		$opponent_user_id_list = Domain_Conversation_Action_UpdateIsAllowedForUserbot::do($userbot_user_id);

		// отправляем ws-событие о том, что бот был включён
		$all_user_id_list = array_merge($all_user_id_list, $opponent_user_id_list);
		Gateway_Bus_Sender::userbotEnabled($userbot_id, $userbot_user_id, array_unique($all_user_id_list));
	}

	/**
	 * событие при отключении бота
	 */
	#[Type_Attribute_EventListener(Type_Event_Userbot_Disabled::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onUserbotDisabled(Struct_Event_Userbot_Disabled $event_data):void {

		$userbot_user_id       = $event_data->userbot_user_id;
		$userbot_id            = $event_data->userbot_id;
		$conversation_map_list = $event_data->conversation_map_list;
		$user_id_list          = $event_data->user_id_list;
		$disabled_at           = $event_data->disabled_at;

		// получаем меты групповых диалогов
		$meta_list = Type_Conversation_Meta::getAll($conversation_map_list);

		// собираем всех пользователей
		$all_user_id_list = $user_id_list;
		foreach ($meta_list as $meta) {

			// получаем участников диалогов
			$meta_user_id_list = array_keys($meta["users"]);
			$all_user_id_list  = array_merge($all_user_id_list, $meta_user_id_list);
		}

		// обновляем флаг is_allowed для сингл-диалогов с ботом
		$opponent_user_id_list = Domain_Conversation_Action_UpdateIsAllowedForUserbot::do($userbot_user_id, ALLOW_STATUS_USERBOT_DISABLED);

		// отправляем ws-событие о том, что бот был выключен
		$all_user_id_list = array_merge($all_user_id_list, $opponent_user_id_list);
		Gateway_Bus_Sender::userbotDisabled($userbot_id, $userbot_user_id, array_unique($all_user_id_list), $disabled_at);
	}

	/**
	 * событие при удалении бота
	 */
	#[Type_Attribute_EventListener(Type_Event_Userbot_Deleted::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onUserbotDeleted(Struct_Event_Userbot_Deleted $event_data):void {

		$userbot_id            = $event_data->userbot_id;
		$userbot_user_id       = $event_data->userbot_user_id;
		$conversation_map_list = $event_data->conversation_map_list;
		$user_id_list          = $event_data->user_id_list;
		$deleted_at            = $event_data->deleted_at;

		// получаем меты диалогов
		$meta_list = Type_Conversation_Meta::getAll($conversation_map_list);

		// собираем всех пользователей
		$all_user_id_list = $user_id_list;

		// проходимся по каждой мете
		foreach ($meta_list as $meta) {

			Helper_Groups::doUserKick($meta, $userbot_user_id, true, $userbot_id);

			// собираем участников диалога
			$meta_user_id_list = array_keys($meta["users"]);
			$all_user_id_list  = array_merge($all_user_id_list, $meta_user_id_list);
		}

		// обновляем флаг is_allowed для сингл-диалогов с ботом
		$opponent_user_id_list = Domain_Conversation_Action_UpdateIsAllowedForUserbot::do($userbot_user_id, ALLOW_STATUS_USERBOT_DELETED);

		// отправляем ws-событие о том, что бот был удалён
		$all_user_id_list = array_merge($all_user_id_list, $opponent_user_id_list);
		Gateway_Bus_Sender::userbotDeleted($userbot_id, $userbot_user_id, array_unique($all_user_id_list), $deleted_at);
	}

	/**
	 * событие при обновлении списка команд бота
	 */
	#[Type_Attribute_EventListener(Type_Event_Userbot_CommandListUpdated::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onCommandListUpdated(Struct_Event_Userbot_CommandListUpdated $event_data):void {

		$userbot               = $event_data->userbot;
		$userbot_user_id       = $event_data->userbot_user_id;
		$conversation_map_list = $event_data->conversation_map_list;
		$user_id_list          = $event_data->user_id_list;

		// получаем меты диалогов
		$meta_list = Type_Conversation_Meta::getAll($conversation_map_list);

		// получаем всё левое меню ботов, для получения всего списка оппонентов
		$left_menu_list        = Type_Conversation_LeftMenu::getOpponents($userbot_user_id, 0, 99999, true);
		$opponent_user_id_list = array_column($left_menu_list, "opponent_user_id");

		// собираем всех пользователей
		$all_user_id_list = $user_id_list;

		// получаем участников групповых диалогов
		foreach ($meta_list as $meta) {

			$meta_user_id_list = array_keys($meta["users"]);
			$all_user_id_list  = array_merge($all_user_id_list, $meta_user_id_list);
		}

		// собираем также и оппонентов из сингл-диалога
		$all_user_id_list = array_merge($all_user_id_list, $opponent_user_id_list);

		// отправляем ws-событие о том, что список команд обновился
		Gateway_Bus_Sender::userbotCommandListUpdated($userbot, $userbot_user_id, array_unique($all_user_id_list));
	}

	/**
	 * событие при редактировании бота
	 */
	#[Type_Attribute_EventListener(Type_Event_Userbot_Edited::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onUserbotEdited(Struct_Event_Userbot_Edited $event_data):void {

		$userbot_user_id       = $event_data->userbot_user_id;
		$userbot               = $event_data->userbot;
		$conversation_map_list = $event_data->conversation_map_list;
		$user_id_list          = $event_data->user_id_list;

		// получаем меты диалогов
		$meta_list = Type_Conversation_Meta::getAll($conversation_map_list);

		// получаем всё левое меню ботов, для получения всего списка оппонентов
		$left_menu_list        = Type_Conversation_LeftMenu::getOpponents($userbot_user_id, 0, 99999, true);
		$opponent_user_id_list = array_column($left_menu_list, "opponent_user_id");

		// собираем всех пользователей
		$all_user_id_list = $user_id_list;

		// получаем участников диалогов
		foreach ($meta_list as $meta) {

			$meta_user_id_list = array_keys($meta["users"]);
			$all_user_id_list  = array_merge($all_user_id_list, $meta_user_id_list);
		}

		// собираем также и оппонентов из сингл-диалога
		$all_user_id_list = array_merge($all_user_id_list, $opponent_user_id_list);

		// отправляем ws-событие о том, что бот был отредактирован
		$all_user_id_list = array_merge($all_user_id_list, $opponent_user_id_list);
		Gateway_Bus_Sender::userbotEdited($userbot, $userbot_user_id, array_unique($all_user_id_list));
	}
}
