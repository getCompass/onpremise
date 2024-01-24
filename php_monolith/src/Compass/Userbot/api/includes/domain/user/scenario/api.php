<?php

namespace Compass\Userbot;

/**
 * Сценарии пользователя для API
 *
 * Class Domain_User_Scenario_Api
 */
abstract class Domain_User_Scenario_Api {

	protected const _API_VERSION = 1;

	/**
	 * отправляем сообщение пользователю
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
	public static function send(string $token, int $user_id, string $type, string|false $text, string|false $file_id):string {

		// проверяем параметры для отправки сообщения
		Domain_Userbot_Action_CheckParamsForSendMessage::do($type, $text, $file_id);

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		$status = Domain_Request_Entity_Request::STATUS_FAILED;
		$action = Domain_Request_Entity_Request::ACTION_SEND_USER;

		try {

			$message_key = Gateway_Socket_Conversation::sendMessageToUser(
				$userbot->userbot_user_id, $user_id, $text, $file_id, $userbot->domino_entrypoint, $userbot->company_id);

			$status      = Domain_Request_Entity_Request::STATUS_SUCCESS;
			$result_data = ["message_id" => $message_key];
		} catch (\cs_Userbot_RequestIncorrectParams) {

			// ошибка, что текст или file_key некорректен
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1000);
		} catch (\cs_Member_IsNotFound) {

			// ошибка, что пользователь не существует
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1001);
		} catch (\cs_Member_IsKicked) {

			// ошибка, что пользователь уволен
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1002);
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
			"user_id"         => $user_id,
			"type"            => $type,
			"text"            => $text,
			"file_key"        => $file_id,
			"company_url"     => $userbot->company_url,
		];
		$request      = Domain_Userbot_Action_AddRequest::do($token, $request_data, $result_data, $status);

		// возвращаем в ответе id запроса
		return $request->request_id;
	}

	/**
	 * получаем список данных о пользователях компании
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
		Domain_User_Entity_Validator::assertParamsForGetUsers($count, $offset);

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		$status = Domain_Request_Entity_Request::STATUS_FAILED;

		try {

			$user_list = Gateway_Socket_Company::getUserList($userbot->userbot_id, $count, $offset, $userbot->domino_entrypoint, $userbot->company_id);

			$status = Domain_Request_Entity_Request::STATUS_SUCCESS;

			$result_data = ["user_list" => static::_formattedUserInfoList($user_list, static::_API_VERSION)];
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			// неизвестная ошибка при выполнении запроса
			$result_data = Domain_Request_Entity_Request::getErrorResponse(Domain_Request_Entity_Request::ACTION_REQUEST, CASE_EXCEPTION_CODE_6);
		}

		// запрос выполнился синхронно, теперь нужно сохранить результаты в таблицу request_list
		// чтобы пользователь мог получить их по переданному request_id
		$request_data = [
			"request_type"    => Domain_Request_Entity_Request::ACTION_GET_USERS,
			"request_version" => static::_API_VERSION,
			"count"           => $count,
			"offset"          => $offset,
			"company_url"     => $userbot->company_url,
			"userbot_id"      => $userbot->userbot_id,
		];
		$request      = Domain_Userbot_Action_AddRequest::do($token, $request_data, $result_data, $status);

		// возвращаем в ответе id запроса
		return $request->request_id;
	}

	/**
	 * приводим к формату данные по пользователям
	 */
	protected static function _formattedUserInfoList(array $user_list, int $request_version = 1):array {

		return match ($request_version) {
			default => Apiv1_Format::userInfoList($user_list),
			2 => Apiv2_Format::userInfoList($user_list),
		};
	}
}