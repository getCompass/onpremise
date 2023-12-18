<?php

namespace Compass\Company;

/**
 * Базовый класс для действия добавления заявки на увольнение сотрудника
 */
class Domain_DismissalRequest_Action_Add {

	/**
	 * Выполняем action
	 *
	 * @throws \queryException
	 * @throws \returnException
	 * @throws cs_DismissalRequestIsAlreadyExist
	 * @throws cs_PlatformNotFound
	 * @throws \parseException
	 */
	public static function do(int $creator_user_id, int $dismissal_user_id, string $user_message_text):array {

		// добавляем $dismissal_user_id в кэш memCache. Если в кэше нету $dismissal_user_id, значит можно добавлять заявку на увольнение
		// это защита от одновременного добавления с разных устройств нескольких заявок на увольнение на один $dismissal_user_id
		Domain_DismissalRequest_Entity_Request::addDismissalRequestUserIdInCache($dismissal_user_id);

		// добавляем заявку
		$dismissal_request = Domain_DismissalRequest_Entity_Request::add($creator_user_id, $dismissal_user_id);

		// отправляем соообщение заявки о увольнении в диалог
		[$hiring_conversation_map, $dismissal_request_message_map, $dismissal_request_thread_map] =
			Gateway_Socket_Conversation::addDismissalRequestMessage($creator_user_id, $dismissal_request->dismissal_request_id, $dismissal_user_id);

		// сохраняем в заявке мапу сообщения из чата и тред к заявке
		$dismissal_request = Domain_DismissalRequest_Action_SetMessageMap::do($dismissal_request->dismissal_request_id, $dismissal_request_message_map);
		$dismissal_request = Domain_DismissalRequest_Action_SetThreadMap::do($dismissal_request->dismissal_request_id, $dismissal_request_thread_map);

		// пушим событие о статусе заявки в go_event
		Gateway_Event_Dispatcher::dispatch(Type_Event_DismissalRequest_Created::create(
			(array) $dismissal_request,
			$user_message_text,
			Type_Api_Platform::getPlatform()
		), true);

		// инкрементим число заявок находящихся в ожидании
		Domain_Company_Entity_Dynamic::inc(Domain_Company_Entity_Dynamic::DISMISSAL_REQUEST_WAITING);

		return [$dismissal_request, $hiring_conversation_map];
	}
}