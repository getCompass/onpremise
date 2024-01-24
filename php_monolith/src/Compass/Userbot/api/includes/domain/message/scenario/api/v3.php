<?php

namespace Compass\Userbot;

/**
 * Сценарии API для сообщений бота
 */
class Domain_Message_Scenario_Api_V3 {

	/**
	 * добавляем реакцию на сообщение
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
	public static function addReaction(string $token, string $message_id, string $reaction):void {

		// проверяем параметры для добавления реакции на сообщение
		Domain_Message_Entity_Validator::assertMessageId($message_id);
		Domain_Message_Entity_Validator::assertReaction($reaction);

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		$action = Domain_Request_Entity_Request::ACTION_ADD_REACTION;

		try {

			Domain_Message_Action_AddReaction::do($userbot->userbot_user_id, $message_id, $reaction, $userbot->domino_entrypoint, $userbot->company_id);
			return;
		} catch (\cs_DecryptHasFailed | \cs_Userbot_RequestIncorrectParams) {
			$exception_code = CASE_EXCEPTION_CODE_1000; // ошибка, что message_key некорректен
		} catch (\cs_Message_IsNotFound) {
			$exception_code = CASE_EXCEPTION_CODE_1007; // ошибка, что такого сообщения не существует
		} catch (\cs_Message_IsNotAllowed) {
			$exception_code = CASE_EXCEPTION_CODE_1005; // ошибка, что у бота нет доступа к сообщению
		} catch (\cs_Reaction_IsNotFound) {
			$exception_code = CASE_EXCEPTION_CODE_1006; // ошибка, что переданная реакция не найдена
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			$exception_code = CASE_EXCEPTION_CODE_6; // неизвестная ошибка при выполнении запроса
			$action         = Domain_Request_Entity_Request::ACTION_REQUEST;
		}

		throw new Domain_Userbot_Exception_RequestFailed("userbot request is failed", Domain_Request_Entity_Request::getErrorResponse($action, $exception_code));
	}

	/**
	 * удаляем реакцию с сообщения
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
	public static function removeReaction(string $token, string $message_id, string $reaction):void {

		// проверяем параметры для удаления реакции с сообщения
		Domain_Message_Entity_Validator::assertMessageId($message_id);
		Domain_Message_Entity_Validator::assertReaction($reaction);

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		$action = Domain_Request_Entity_Request::ACTION_REMOVE_REACTION;

		try {

			Domain_Message_Action_RemoveReaction::do($userbot->userbot_user_id, $message_id, $reaction, $userbot->domino_entrypoint, $userbot->company_id);
			return;
		} catch (\cs_DecryptHasFailed | \cs_Userbot_RequestIncorrectParams) {
			$exception_code = CASE_EXCEPTION_CODE_1000; // ошибка, что message_key некорректен
		} catch (\cs_Message_IsNotFound) {
			$exception_code = CASE_EXCEPTION_CODE_1007; // ошибка, что такого сообщения не существует
		} catch (\cs_Message_IsNotAllowed) {
			$exception_code = CASE_EXCEPTION_CODE_1005; // ошибка, что у бота нет доступа к сообщению
		} catch (\cs_Reaction_IsNotFound) {
			$exception_code = CASE_EXCEPTION_CODE_1006; // ошибка, что переданная реакция не найдена
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			$exception_code = CASE_EXCEPTION_CODE_6; // неизвестная ошибка при выполнении запроса
			$action         = Domain_Request_Entity_Request::ACTION_REQUEST;
		}

		throw new Domain_Userbot_Exception_RequestFailed("userbot request is failed", Domain_Request_Entity_Request::getErrorResponse($action, $exception_code));
	}
}