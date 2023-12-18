<?php

namespace Compass\Userbot;

/**
 * Сценарии webhook бота для API
 */
class Domain_Webhook_Scenario_Api_V3 {

	/**
	 * устанавливаем версию webhook бота
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
	public static function setVersion(string $token, int $version):void {

		// проверяем параметры на корректность
		if ($version < 1) {
			throw new \cs_Userbot_RequestIncorrect("incorrect version = {$version}");
		}

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		$action = Domain_Request_Entity_Request::ACTION_SET_WEBHOOK_VERSION;

		try {

			Gateway_Socket_Pivot::setWebhookVersion($userbot->userbot_id, $token, $version);
			return;
		} catch (\cs_Userbot_RequestIncorrect) {
			$exception_code = CASE_EXCEPTION_CODE_1011; // передана некорректная версия для webhook бота
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			$exception_code = CASE_EXCEPTION_CODE_6; // неизвестная ошибка при выполнении запроса
			$action         = Domain_Request_Entity_Request::ACTION_REQUEST;
		}

		// неизвестная ошибка при выполнении запроса
		throw new Domain_Userbot_Exception_RequestFailed("userbot request is failed", Domain_Request_Entity_Request::getErrorResponse($action, $exception_code));
	}

	/**
	 * получаем версию webhook бота
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_Userbot_IsNotEnabled
	 * @throws \cs_Userbot_NotFound
	 * @throws \userAccessException
	 */
	public static function getVersion(string $token):int {

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		return Domain_Userbot_Entity_Userbot::getWebhookVersion($userbot->extra);
	}
}