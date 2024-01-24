<?php

declare(strict_types = 1);

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Агрегатор подписок на событие для домена member.
 */
class Domain_Member_Scenario_Event {

	/**
	 * Пользователь получил сущность карточки (спасибо/требовательность/достижение)
	 * @long
	 */
	#[Type_Attribute_EventListener(Type_Event_Member_OnUserReceivedEmployeeCardEntity::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onUserReceivedEmployeeCardEntity(Struct_Event_Member_OnUserReceivedEmployeeCardEntity $event_data):void {

		$entity_type      = $event_data->entity_type;
		$sender_user_id   = $event_data->sender_user_id;
		$received_user_id = $event_data->received_user_id;
		$message_map      = $event_data->message_map;
		$week_count       = $event_data->week_count;
		$month_count      = $event_data->month_count;

		// создаём тред к сообщению-достижению
		try {
			$thread_meta_row = Domain_Thread_Action_AddToConversationMessage::do($received_user_id, $message_map);
		} catch (cs_Message_HaveNotAccess) {
			return;
		}

		// формируем новое системное сообщение, исходя из типа сущности
		$message_list        = [];
		$silent_message_list = [];
		switch ($entity_type) {

			case "respect":

				$message_list[] = Type_Thread_Message_Main::getLastVersionHandler()::makeSystemUserReceivedRespect($received_user_id);
				break;

			case "exactingness":

				$message_list[] = Type_Thread_Message_Main::getLastVersionHandler()::makeSystemUserReceivedExactingness($received_user_id);

				if (IS_NEED_SEND_EXACTNESS_COUNT_THREAD_MESSAGE) {
					$silent_message_list[] = Type_Thread_Message_Main::getLastVersionHandler()::makeSystemUserAddedExactingness($sender_user_id, $week_count, $month_count);
				}
				break;

			case "achievement":

				$message_list[] = Type_Thread_Message_Main::getLastVersionHandler()::makeSystemUserReceivedAchievement($received_user_id);
				break;
		}

		// отправляем сообщения в тред
		try {
			Domain_Thread_Action_Message_AddList::do($thread_meta_row["thread_map"], $thread_meta_row, $message_list);
		} catch (Domain_Thread_Exception_Message_ListIsEmpty) {
			throw new ParseFatalException("passed empty message list");
		}

		// отправляем бесшумно сообщения в тред (только в кейсе с требовательностью)
		try {
			Domain_Thread_Action_Message_AddList::do($thread_meta_row["thread_map"], $thread_meta_row, $silent_message_list, is_silent: true);
		} catch (Domain_Thread_Exception_Message_ListIsEmpty) {
			// ничего не делаем
		}
	}
}