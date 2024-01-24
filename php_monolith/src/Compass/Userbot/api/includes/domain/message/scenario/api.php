<?php

namespace Compass\Userbot;

/**
 * Сценарии сообщений для API
 *
 * Class Domain_Message_Scenario_Api
 */
abstract class Domain_Message_Scenario_Api {

	protected const _API_VERSION = 1;

	/**
	 * добавляем реакцию на сообщение
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
	public static function addReaction(string $token, string $message_id, string $reaction):string {

		// проверяем параметры для добавления реакции на сообщение
		Domain_Message_Entity_Validator::assertMessageId($message_id);
		Domain_Message_Entity_Validator::assertReaction($reaction);

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		$status = Domain_Request_Entity_Request::STATUS_FAILED;
		$action = Domain_Request_Entity_Request::ACTION_ADD_REACTION;

		try {

			Domain_Message_Action_AddReaction::do($userbot->userbot_user_id, $message_id, $reaction, $userbot->domino_entrypoint, $userbot->company_id);

			$status      = Domain_Request_Entity_Request::STATUS_SUCCESS;
			$result_data = [];
		} catch (\cs_DecryptHasFailed | \cs_Userbot_RequestIncorrectParams) {

			// ошибка, что message_key некорректен
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1000);
		} catch (\cs_Message_IsNotFound) {

			// ошибка, что такого сообщения не существует
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1007);
		} catch (\cs_Message_IsNotAllowed) {

			// ошибка, что у бота нет доступа к сообщению
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1005);
		} catch (\cs_Reaction_IsNotFound) {

			// ошибка, что переданная реакция не найдена
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1006);
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			// неизвестная ошибка при выполнении запроса
			$result_data = Domain_Request_Entity_Request::getErrorResponse(Domain_Request_Entity_Request::ACTION_REQUEST, CASE_EXCEPTION_CODE_6);
		}

		// запрос выполнился синхронно, теперь нужно сохранить результаты в таблицу request_list
		// чтобы пользователь мог получить их по переданному request_id
		$request_data = [
			"request_type"    => $action,
			"request_version" => static::_API_VERSION,
			"userbot_user_id" => $userbot->userbot_user_id,
			"message_key"     => $message_id,
			"reaction"        => $reaction,
			"company_url"     => $userbot->company_url,
		];
		$request = Domain_Userbot_Action_AddRequest::do($token, $request_data, $result_data, $status);

		// возвращаем в ответе id запроса
		return $request->request_id;
	}

	/**
	 * удаляем реакцию с сообщения
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
	public static function removeReaction(string $token, string $message_id, string $reaction):string {

		// проверяем параметры для удаления реакции с сообщения
		Domain_Message_Entity_Validator::assertMessageId($message_id);
		Domain_Message_Entity_Validator::assertReaction($reaction);

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		$status      = Domain_Request_Entity_Request::STATUS_FAILED;
		$action      = Domain_Request_Entity_Request::ACTION_REMOVE_REACTION;
		$result_data = [];

		try {

			Domain_Message_Action_RemoveReaction::do($userbot->userbot_user_id, $message_id, $reaction, $userbot->domino_entrypoint, $userbot->company_id);

			$status = Domain_Request_Entity_Request::STATUS_SUCCESS;
		} catch (\cs_DecryptHasFailed | \cs_Userbot_RequestIncorrectParams) {

			// ошибка, что message_key некорректен
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1000);
		} catch (\cs_Message_IsNotFound) {

			// ошибка, что такого сообщения не существует
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1007);
		} catch (\cs_Message_IsNotAllowed) {

			// ошибка, что у бота нет доступа к сообщению
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1005);
		} catch (\cs_Reaction_IsNotFound) {

			// ошибка, что переданная реакция не найдена
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1006);
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			// неизвестная ошибка при выполнении запроса
			$result_data = Domain_Request_Entity_Request::getErrorResponse(Domain_Request_Entity_Request::ACTION_REQUEST, CASE_EXCEPTION_CODE_6);
		}

		// запрос выполнился синхронно, теперь нужно сохранить результаты в таблицу request_list
		// чтобы пользователь мог получить их по переданному request_id
		$request_data = [
			"request_type"    => $action,
			"request_version" => static::_API_VERSION,
			"userbot_user_id" => $userbot->userbot_user_id,
			"message_key"     => $message_id,
			"reaction"        => $reaction,
			"company_url"     => $userbot->company_url,
		];
		$request = Domain_Userbot_Action_AddRequest::do($token, $request_data, $result_data, $status);

		// возвращаем в ответе id запроса
		return $request->request_id;
	}
}