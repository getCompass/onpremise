<?php

namespace Compass\Userbot;

/**
 * Сценарии API для пользователей бота
 */
class Domain_User_Scenario_Api_V3 {

	/**
	 * отправляем сообщение пользователю
	 *
	 * @throws Domain_Userbot_Exception_RequestFailed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_Userbot_IsNotEnabled
	 * @throws \cs_Userbot_NotFound
	 * @throws \cs_Userbot_RequestIncorrect
	 * @throws \userAccessException
	 */
	public static function send(string $token, int $user_id, string $type, string|false $text, string|false $file_id):string {

		// проверяем параметры для отправки сообщения
		Domain_Userbot_Action_CheckParamsForSendMessage::do($type, $text, $file_id);

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		// значения по умолчанию
		$action = Domain_Request_Entity_Request::ACTION_SEND_USER;

		try {
			return Gateway_Socket_Conversation::sendMessageToUser($userbot->userbot_user_id, $user_id, $text, $file_id, $userbot->domino_entrypoint, $userbot->company_id);
		} catch (\cs_Userbot_RequestIncorrectParams) {
			$exception_code = CASE_EXCEPTION_CODE_1000; // ошибка, что текст или file_key некорректен
		} catch (\cs_Member_IsNotFound) {
			$exception_code = CASE_EXCEPTION_CODE_1001; // ошибка, что пользователь не существует
		} catch (\cs_Member_IsKicked) {
			$exception_code = CASE_EXCEPTION_CODE_1002; // ошибка, что пользователь уволен
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			$exception_code = CASE_EXCEPTION_CODE_6; // неизвестная ошибка при выполнении запроса
			$action         = Domain_Request_Entity_Request::ACTION_REQUEST;
		}

		throw new Domain_Userbot_Exception_RequestFailed("userbot request is failed", Domain_Request_Entity_Request::getErrorResponse($action, $exception_code));
	}

	/**
	 * получаем список данных о пользователях компании
	 *
	 * @throws Domain_Userbot_Exception_RequestFailed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_Userbot_IsNotEnabled
	 * @throws \cs_Userbot_NotFound
	 * @throws \cs_Userbot_RequestIncorrect
	 * @throws \userAccessException
	 */
	public static function getList(string $token, int $count, int $offset):array {

		// проверяем параметры на корректность
		Domain_User_Entity_Validator::assertParamsForGetUsers($count, $offset);

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		try {
			return Gateway_Socket_Company::getUserList($userbot->userbot_id, $count, $offset, $userbot->domino_entrypoint, $userbot->company_id);
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			// неизвестная ошибка при выполнении запроса
			throw new Domain_Userbot_Exception_RequestFailed(
				"userbot request is failed",
				Domain_Request_Entity_Request::getErrorResponse(Domain_Request_Entity_Request::ACTION_REQUEST, CASE_EXCEPTION_CODE_6)
			);
		}
	}
}