<?php

namespace Compass\Userbot;

/**
 * Сценарии API для тредов бота
 */
class Domain_Thread_Scenario_Api_V3 {

	/**
	 * отправляем сообщение в тред
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
	public static function send(string $token, string $message_id, string $type, string|false $text, string|false $file_id):string {

		Domain_Message_Entity_Validator::assertMessageId($message_id);

		// проверяем параметры для отправки сообщения
		Domain_Userbot_Action_CheckParamsForSendMessage::do($type, $text, $file_id);

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		$action = Domain_Request_Entity_Request::ACTION_SEND_THREAD;

		try {

			return Gateway_Socket_Thread::sendMessageToThread(
				$userbot->userbot_user_id, $message_id, $text, $file_id, $userbot->domino_entrypoint, $userbot->company_id);
		} catch (\cs_Userbot_RequestIncorrectParams) {
			$exception_code = CASE_EXCEPTION_CODE_1000; // ошибка, что текст или file_key некорректен
		} catch (\cs_Message_IsNotFound) {
			$exception_code = CASE_EXCEPTION_CODE_1007; // ошибка, что такого сообщения не существует
		} catch (\cs_Message_IsNotAllowed) {
			$exception_code = CASE_EXCEPTION_CODE_1005; // ошибка, что у бота нет доступа к сообщению
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			$exception_code = CASE_EXCEPTION_CODE_6; // неизвестная ошибка при выполнении запроса
			$action         = Domain_Request_Entity_Request::ACTION_REQUEST;
		}

		throw new Domain_Userbot_Exception_RequestFailed("userbot request is failed", Domain_Request_Entity_Request::getErrorResponse($action, $exception_code));
	}
}