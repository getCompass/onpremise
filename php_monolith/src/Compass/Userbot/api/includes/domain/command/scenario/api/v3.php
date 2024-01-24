<?php

namespace Compass\Userbot;

/**
 * Сценарии API для команд бота для
 */
class Domain_Command_Scenario_Api_V3 {

	/**
	 * обновить список команд бота
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
	public static function update(string $token, array $command_list):void {

		// проверяем количество команд для бота
		Domain_Command_Entity_Validator::assertCorrectCommandsLimit($command_list);

		// каждую команду проверяем на максимальную длину
		Domain_Command_Entity_Validator::assertCorrectCommandLength($command_list);

		// проверяем, что нет повторяющихся команд
		Domain_Command_Entity_Validator::assertCommandDuplicate($command_list);

		// получаем бота
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		$action = Domain_Request_Entity_Request::ACTION_UPDATE_COMMANDS;

		try {

			Gateway_Socket_Company::updateCommandList($userbot->userbot_id, $command_list, $userbot->domino_entrypoint, $userbot->company_id);
			return;
		} catch (\cs_UserbotCommand_ExceededLimit) {
			$exception_code = CASE_EXCEPTION_CODE_1008; // превышен лимит списка команд
		} catch (\cs_UserbotCommand_IsIncorrect) {
			$exception_code = CASE_EXCEPTION_CODE_1009; // некорректная команда в списке
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			$exception_code = CASE_EXCEPTION_CODE_6; // неизвестная ошибка при выполнении запроса
			$action         = Domain_Request_Entity_Request::ACTION_REQUEST;
		}

		throw new Domain_Userbot_Exception_RequestFailed("userbot request is failed", Domain_Request_Entity_Request::getErrorResponse($action, $exception_code));
	}

	/**
	 * получить список команд бота
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_Userbot_IsNotEnabled
	 * @throws \cs_Userbot_NotFound
	 * @throws \userAccessException
	 * @throws Domain_Userbot_Exception_RequestFailed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getList(string $token):array {

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		try {

			return Gateway_Socket_Company::getCommandList($userbot->userbot_id, $userbot->domino_entrypoint, $userbot->company_id);
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			// неизвестная ошибка при выполнении запроса
			throw new Domain_Userbot_Exception_RequestFailed(
				"userbot request is failed",
				Domain_Request_Entity_Request::getErrorResponse(Domain_Request_Entity_Request::ACTION_REQUEST, CASE_EXCEPTION_CODE_6)
			);
		}
	}
}