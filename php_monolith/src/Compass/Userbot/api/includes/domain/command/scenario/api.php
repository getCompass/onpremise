<?php

namespace Compass\Userbot;

/**
 * Сценарии команд бота для API
 *
 * Class Domain_Command_Scenario_Api
 */
abstract class Domain_Command_Scenario_Api {

	protected const _API_VERSION = 1;

	/**
	 * обновить список команд бота
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
	public static function update(string $token, array $command_list):string {

		// проверяем количество команд для бота
		Domain_Command_Entity_Validator::assertCorrectCommandsLimit($command_list);

		// каждую команду проверяем на максимальную длину
		Domain_Command_Entity_Validator::assertCorrectCommandLength($command_list);

		// проверяем, что нет повторяющихся команд
		Domain_Command_Entity_Validator::assertCommandDuplicate($command_list);

		// получаем бота
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		// значения по умолчанию
		$status      = Domain_Request_Entity_Request::STATUS_FAILED;
		$action      = Domain_Request_Entity_Request::ACTION_UPDATE_COMMANDS;
		$result_data = [];

		try {

			Gateway_Socket_Company::updateCommandList($userbot->userbot_id, $command_list, $userbot->domino_entrypoint, $userbot->company_id);

			$status = Domain_Request_Entity_Request::STATUS_SUCCESS;
		} catch (\cs_UserbotCommand_ExceededLimit) {

			// превышен лимит списка команд
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1008);
		} catch (\cs_UserbotCommand_IsIncorrect) {

			// некорректная команда в списке
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1009);
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error $e) {

			// неизвестная ошибка при выполнении запроса
			$result_data = Domain_Request_Entity_Request::getErrorResponse(Domain_Request_Entity_Request::ACTION_REQUEST, CASE_EXCEPTION_CODE_6);

		}

		// запрос выполнился синхронно, теперь нужно сохранить результаты в таблицу request_list
		// чтобы пользователь мог получить их по переданному request_id
		$request_data = [
			"request_type"    => $action,
			"request_version" => static::_API_VERSION,
			"command_list"    => $command_list,
			"userbot_id"      => $userbot->userbot_id,
			"company_url"     => $userbot->company_url,
		];
		$request = Domain_Userbot_Action_AddRequest::do($token, $request_data, $result_data, $status);

		// возвращаем в ответе id запроса
		return $request->request_id;
	}

	/**
	 * получить список команд бота
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_Userbot_IsNotEnabled
	 * @throws \cs_Userbot_NotFound
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \userAccessException
	 */
	public static function getList(string $token):string {

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		$status = Domain_Request_Entity_Request::STATUS_FAILED;

		try {

			$command_list = Gateway_Socket_Company::getCommandList($userbot->userbot_id, $userbot->domino_entrypoint, $userbot->company_id);

			$status      = Domain_Request_Entity_Request::STATUS_SUCCESS;
			$result_data = ["command_list" => $command_list];
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			// неизвестная ошибка при выполнении запроса
			$result_data = Domain_Request_Entity_Request::getErrorResponse(Domain_Request_Entity_Request::ACTION_REQUEST, CASE_EXCEPTION_CODE_6);
		}

		// запрос выполнился синхронно, теперь нужно сохранить результаты в таблицу request_list
		// чтобы пользователь мог получить их по переданному request_id
		$request_data = [
			"request_type"    => Domain_Request_Entity_Request::ACTION_GET_COMMANDS,
			"request_version" => static::_API_VERSION,
			"company_url"     => $userbot->company_url,
			"userbot_id"      => $userbot->userbot_id,
		];
		$request = Domain_Userbot_Action_AddRequest::do($token, $request_data, $result_data, $status);

		// возвращаем в ответе id запроса
		return $request->request_id;
	}
}