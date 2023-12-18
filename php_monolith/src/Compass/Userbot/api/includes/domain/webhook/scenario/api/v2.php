<?php

namespace Compass\Userbot;

/**
 * Сценарии webhook бота для API
 */
class Domain_Webhook_Scenario_Api_V2 {

	protected const _API_VERSION = 2;

	/**
	 * устанавливаем версию webhook бота
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
	public static function setVersion(string $token, int $version):string {

		// проверяем параметры на корректность
		if ($version < 1) {
			throw new \cs_Userbot_RequestIncorrect("incorrect version = {$version}");
		}

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		$status      = Domain_Request_Entity_Request::STATUS_FAILED;
		$action      = Domain_Request_Entity_Request::ACTION_SET_WEBHOOK_VERSION;
		$result_data = [];

		try {

			Gateway_Socket_Pivot::setWebhookVersion($userbot->userbot_id, $token, $version);

			$status = Domain_Request_Entity_Request::STATUS_SUCCESS;
		} catch (\cs_Userbot_RequestIncorrect) {

			// передана некорректная версия для webhook бота
			$result_data = Domain_Request_Entity_Request::getErrorResponse($action, CASE_EXCEPTION_CODE_1011);
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			// неизвестная ошибка при выполнении запроса
			$result_data = Domain_Request_Entity_Request::getErrorResponse(Domain_Request_Entity_Request::ACTION_REQUEST, CASE_EXCEPTION_CODE_6);
		}

		// запрос выполнился синхронно, теперь нужно сохранить результаты в таблицу request_list
		// чтобы пользователь мог получить их по переданному request_id
		$request_data = [
			"request_type"    => $action,
			"request_version" => static::_API_VERSION,
			"userbot_id"      => $userbot->userbot_id,
			"token"           => $userbot->token,
			"version"         => $version,
		];
		$request = Domain_Userbot_Action_AddRequest::do($token, $request_data, $result_data, $status);

		// возвращаем в ответе id запроса
		return $request->request_id;
	}

	/**
	 * получаем версию webhook бота
	 */
	public static function getVersion(string $token):int {

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		return Domain_Userbot_Entity_Userbot::getWebhookVersion($userbot->extra);
	}
}