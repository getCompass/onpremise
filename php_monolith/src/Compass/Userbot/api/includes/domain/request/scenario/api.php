<?php

namespace Compass\Userbot;

/**
 * Сценарии запросов внешних сервисов для API
 *
 * Class Domain_Request_Scenario_Api
 */
class Domain_Request_Scenario_Api {

	/**
	 * получить данные выполнения запроса внешнего сервиса
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_Userbot_IsNotEnabled
	 * @throws \cs_Userbot_NotFound
	 * @throws \cs_Userbot_RequestIncorrect
	 * @throws \userAccessException
	 */
	public static function get(string $token, string $request_id):array {

		// проверяем параметр request_id на корректность
		if (isEmptyString($request_id)) {
			throw new \cs_Userbot_RequestIncorrect("passed empty request_id");
		}

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		// получаем запрос
		$request = Gateway_Db_UserbotMain_RequestList::get($request_id, $token);

		return [$request->status, $request->result_data];
	}
}