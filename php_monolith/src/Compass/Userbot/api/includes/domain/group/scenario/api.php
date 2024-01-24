<?php

namespace Compass\Userbot;

/**
 * Сценарии групп для API
 *
 * Class Domain_Group_Scenario_Api
 */
abstract class Domain_Group_Scenario_Api {

	protected const _API_VERSION = 1;

	/**
	 * отправляем сообщение в группу
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_Userbot_IsNotEnabled
	 * @throws \cs_Userbot_NotFound
	 * @throws \cs_Userbot_RequestIncorrect
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \userAccessException
	 * @long
	 */
	public static function send(string $token, string $group_id, string $type, string|false $text, string|false $file_id):string {

		// проверяем параметры для отправки сообщения
		Domain_Group_Entity_Validator::assertGroupId($group_id);
		Domain_Userbot_Action_CheckParamsForSendMessage::do($type, $text, $file_id);

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		$status = Domain_Request_Entity_Request::STATUS_FAILED;
		$action = Domain_Request_Entity_Request::ACTION_SEND_GROUP;

		try {

			$message_key = Gateway_Socket_Conversation::sendMessageToGroup($userbot->userbot_user_id, $group_id, $text, $file_id, $userbot->domino_entrypoint, $userbot->company_id);

			$result_data = ["message_id" => $message_key];
			$status      = Domain_Request_Entity_Request::STATUS_SUCCESS;
		} catch (\cs_Userbot_RequestIncorrectParams | \cs_Conversation_IsNotGroup) {

			// ошибка, что текст, file_key или conversation_key некорректен
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1000);
		} catch (\cs_Conversation_IsNotFound) {

			// ошибка, что такой группы не существует
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1004);
		} catch (\cs_Conversation_IsNotAllowed) {

			// ошибка, что у бота нет доступа к группе
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1003);
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			// неизвестная ошибка при выполнении запроса
			$result_data = Domain_Request_Entity_Request::getErrorResponse(Domain_Request_Entity_Request::ACTION_REQUEST, CASE_EXCEPTION_CODE_6);
		}

		// запрос выполнился синхронно, теперь нужно сохранить результаты в таблицу request_list
		// чтобы пользователь мог получить их по переданному request_id
		$request_data = [
			"request_type"     => Domain_Request_Entity_Request::ACTION_SEND_GROUP,
			"request_version"  => static::_API_VERSION,
			"userbot_user_id"  => $userbot->userbot_user_id,
			"conversation_key" => $group_id,
			"type"             => $type,
			"text"             => $text,
			"file_key"         => $file_id,
			"company_url"      => $userbot->company_url,
		];
		$request = Domain_Userbot_Action_AddRequest::do($token, $request_data, $result_data, $status);

		// возвращаем в ответе id запроса
		return $request->request_id;
	}

	/**
	 * получить список групп бота
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_Userbot_IsNotEnabled
	 * @throws \cs_Userbot_NotFound
	 * @throws \cs_Userbot_RequestIncorrect
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \userAccessException
	 */
	public static function getList(string $token, int $count, int $offset):string {

		// проверяем параметры на корректность
		Domain_Group_Entity_Validator::assertParamsForGetGroups($count, $offset);

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		$status = Domain_Request_Entity_Request::STATUS_FAILED;

		try {

			$group_info_list = Gateway_Socket_Company::getGroupList($userbot->userbot_id, $userbot->domino_entrypoint, $userbot->company_id);

			$status      = Domain_Request_Entity_Request::STATUS_SUCCESS;
			$result_data = ["group_list" => Apiv2_Format::groupInfoList($group_info_list)];
		} catch (\cs_Userbot_RequestFailed|\Exception|\Error) {

			// неизвестная ошибка при выполнении запроса
			$result_data = Domain_Request_Entity_Request::getErrorResponse(Domain_Request_Entity_Request::ACTION_REQUEST, CASE_EXCEPTION_CODE_6);
		}

		// запрос выполнился синхронно, теперь нужно сохранить результаты в таблицу request_list
		// чтобы пользователь мог получить их по переданному request_id
		$request_data = [
			"request_type"    => Domain_Request_Entity_Request::ACTION_GET_GROUPS,
			"request_version" => static::_API_VERSION,
			"count"           => $count,
			"offset"          => $offset,
			"company_url"     => $userbot->company_url,
			"userbot_id"      => $userbot->userbot_id,
		];
		$request = Domain_Userbot_Action_AddRequest::do($token, $request_data, $result_data, $status);

		// возвращаем в ответе id запроса
		return $request->request_id;
	}
}