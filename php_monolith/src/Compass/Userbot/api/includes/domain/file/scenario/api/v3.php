<?php

namespace Compass\Userbot;

/**
 * Сценарии API для файлов бота
 */
class Domain_File_Scenario_Api_V3 {

	/**
	 * получить url файловой ноды
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
	public static function getUrl(string $token):array {

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		try {

			return Gateway_Socket_FileBalancer::getNodeUrl($userbot->userbot_user_id, $userbot->domino_entrypoint, $userbot->company_id);
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			throw new Domain_Userbot_Exception_RequestFailed(
				"userbot request is failed",
				Domain_Request_Entity_Request::getErrorResponse(Domain_Request_Entity_Request::ACTION_REQUEST, CASE_EXCEPTION_CODE_6)
			);
		}
	}
}