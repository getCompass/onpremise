<?php

namespace Compass\Userbot;

/**
 * Сценарии API для групп бота
 */
class Domain_Group_Scenario_Api_V3 {

	/**
	 * отправляем сообщение в группу
	 *
	 * @throws Domain_Userbot_Exception_RequestFailed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_Userbot_IsNotEnabled
	 * @throws \cs_Userbot_NotFound
	 * @throws \cs_Userbot_RequestIncorrect
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

		$action = Domain_Request_Entity_Request::ACTION_SEND_GROUP;

		try {

			return Gateway_Socket_Conversation::sendMessageToGroup($userbot->userbot_user_id, $group_id, $text, $file_id, $userbot->domino_entrypoint, $userbot->company_id);
		} catch (\cs_Userbot_RequestIncorrectParams | \cs_Conversation_IsNotGroup) {
			$exception_code = CASE_EXCEPTION_CODE_1000; // ошибка, что текст, file_key или conversation_key некорректен
		} catch (\cs_Conversation_IsNotFound) {
			$exception_code = CASE_EXCEPTION_CODE_1004; // ошибка, что такой группы не существует
		} catch (\cs_Conversation_IsNotAllowed) {
			$exception_code = CASE_EXCEPTION_CODE_1003; // ошибка, что у бота нет доступа к группе
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			$exception_code = CASE_EXCEPTION_CODE_6; // неизвестная ошибка при выполнении запроса
			$action         = Domain_Request_Entity_Request::ACTION_REQUEST;
		}

		throw new Domain_Userbot_Exception_RequestFailed("userbot request is failed", Domain_Request_Entity_Request::getErrorResponse($action, $exception_code));
	}

	/**
	 * получить список групп бота
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
		Domain_Group_Entity_Validator::assertParamsForGetGroups($count, $offset);

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		try {

			return Gateway_Socket_Company::getGroupList($userbot->userbot_id, $userbot->domino_entrypoint, $userbot->company_id);
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			// неизвестная ошибка при выполнении запроса
			throw new Domain_Userbot_Exception_RequestFailed(
				"userbot request is failed",
				Domain_Request_Entity_Request::getErrorResponse(Domain_Request_Entity_Request::ACTION_REQUEST, CASE_EXCEPTION_CODE_6)
			);
		}
	}
}