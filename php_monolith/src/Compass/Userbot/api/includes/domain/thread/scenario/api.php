<?php

namespace Compass\Userbot;

/**
 * Сценарии тредов для API
 *
 * Class Domain_Group_Scenario_Api
 */
abstract class Domain_Thread_Scenario_Api {

	protected const _API_VERSION = 1;

	/**
	 * отправляем сообщение в тред
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
	public static function send(string $token, string $message_id, string $type, string|false $text, string|false $file_id):string {

		Domain_Message_Entity_Validator::assertMessageId($message_id);

		// проверяем параметры для отправки сообщения
		Domain_Userbot_Action_CheckParamsForSendMessage::do($type, $text, $file_id);

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		$status = Domain_Request_Entity_Request::STATUS_FAILED;
		$action = Domain_Request_Entity_Request::ACTION_SEND_THREAD;

		try {

			$message_key = Gateway_Socket_Thread::sendMessageToThread(
				$userbot->userbot_user_id, $message_id, $text, $file_id, $userbot->domino_entrypoint, $userbot->company_id);
			$result_data = ["message_id" => $message_key];

			$status = Domain_Request_Entity_Request::STATUS_SUCCESS;
		} catch (\cs_Userbot_RequestIncorrectParams) {

			// ошибка, что текст или file_key некорректен
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1000);
		} catch (\cs_Message_IsNotFound) {

			// ошибка, что такого сообщения не существует
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1007);
		} catch (\cs_Message_IsNotAllowed) {

			// ошибка, что у бота нет доступа к сообщению
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1005);
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
			"type"            => $type,
			"text"            => $text,
			"file_key"        => $file_id,
			"company_url"     => $userbot->company_url,
		];
		$request = Domain_Userbot_Action_AddRequest::do($token, $request_data, $result_data, $status);

		// возвращаем в ответе id запроса
		return $request->request_id;
	}
}