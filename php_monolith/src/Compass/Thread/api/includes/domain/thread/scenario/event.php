<?php

declare(strict_types = 1);

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Агрегатор подписок на событие для домена conversation.
 */
class Domain_Thread_Scenario_Event {

	/**
	 * Нужно распарсить ссылку и добавить preview.
	 *
	 * @param Struct_Event_Thread_LinkParseRequired $event_data
	 *
	 * @throws Domain_Thread_Exception_Preview_IncorrectUrl
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	#[Type_Attribute_EventListener(Type_Event_Thread_LinkParseRequired::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onLinkParseRequired(Struct_Event_Thread_LinkParseRequired $event_data):void {

		$worker = new Type_Preview_Worker();
		$worker->doWork(
			$event_data->message_map,
			$event_data->user_id,
			$event_data->link_list,
			$event_data->lang,
			$event_data->user_list,
			$event_data->need_full_preview,
			$event_data->parent_conversation_map
		);
	}

	/**
	 * создали заявку увольнения
	 *
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ThreadIsReadOnly
	 */
	#[Type_Attribute_EventListener(Type_Event_DismissalRequest_Created::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onDismissalRequestCreated(Struct_Event_DismissalRequest_Created $event_data):void {

		$dismissal_request = $event_data->dismissal_request;

		// если нет треда, создаем
		if (isEmptyString($dismissal_request["extra"]["extra"]["thread_map"])) {

			$thread_meta_row                                   = Domain_Thread_Action_AddToDismissalRequest::do(
				$dismissal_request["creator_user_id"], $dismissal_request["dismissal_request_id"], true
			);
			$dismissal_request["extra"]["extra"]["thread_map"] = $thread_meta_row["thread_map"];
		}

		// выполняем действия при создании заявки
		try {

			Domain_Thread_Scenario_Socket::onCreateDismissalRequest(
				(int) $dismissal_request["creator_user_id"],
				PARENT_ENTITY_TYPE_DISMISSAL_REQUEST,
				$event_data->user_comment,
				(string) $dismissal_request["extra"]["extra"]["thread_map"],
				$event_data->user_platform,
			);
		} catch (Domain_Thread_Exception_Message_ListIsEmpty) {
			throw new ParseFatalException("passed empty message list");
		}
	}

	/**
	 * создали заявку на самоувольнение
	 *
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ThreadIsReadOnly
	 */
	#[Type_Attribute_EventListener(Type_Event_DismissalRequest_SelfCreated::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onDismissalRequestSelfCreated(Struct_Event_DismissalRequest_SelfCreated $event_data):void {

		$dismissal_request = $event_data->dismissal_request;

		// если нет треда, создаем
		if (isEmptyString($dismissal_request["extra"]["extra"]["thread_map"])) {

			$thread_meta_row                                   = Domain_Thread_Action_AddToDismissalRequest::do(
				$dismissal_request["creator_user_id"], $dismissal_request["dismissal_request_id"], true
			);
			$dismissal_request["extra"]["extra"]["thread_map"] = $thread_meta_row["thread_map"];
		}

		// выполняем действия при создании заявки на самоувольнение
		try {

			$thread_meta_row = Domain_Thread_Scenario_Socket::onCreateDismissalRequest(
				(int) $dismissal_request["creator_user_id"],
				PARENT_ENTITY_TYPE_DISMISSAL_REQUEST,
				"",
				(string) $dismissal_request["extra"]["extra"]["thread_map"],
				is_dismissal_self: true
			);
		} catch (Domain_Thread_Exception_Message_ListIsEmpty) {
			throw new ParseFatalException("passed empty message list");
		}

		// добавляем в тред сообщение о том, что сотрудник покинул компанию
		$message_list[] = Type_Thread_Message_Main::getLastVersionHandler()::makeSystemDismissalRequestOnUserLeftCompany($dismissal_request["creator_user_id"]);

		try {
			Domain_Thread_Action_Message_AddList::do($thread_meta_row["thread_map"], $thread_meta_row, $message_list);
		} catch (Domain_Thread_Exception_Message_ListIsEmpty) {
			throw new ParseFatalException("passed empty message list");
		}
	}

	/**
	 * Нужно обработать ссылки и выявить конечные адреса
	 *
	 * @param Struct_Event_Thread_OnClearConversationForUserList $event_data
	 *
	 */
	#[Type_Attribute_EventListener(Type_Event_Thread_OnClearConversationForUserList::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onClearConversationForUserList(Struct_Event_Thread_OnClearConversationForUserList $event_data):void {

		$user_id_list     = $event_data->user_id_list;
		$conversation_map = $event_data->conversation_map;

		foreach ($user_id_list as $user_id) {
			Type_Thread_Menu::nullifyUnreadCountByMetaMap($user_id, $conversation_map);
		}
	}
}