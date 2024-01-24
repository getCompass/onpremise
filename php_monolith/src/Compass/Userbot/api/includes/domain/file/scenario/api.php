<?php

namespace Compass\Userbot;

/**
 * Сценарии файлов для API
 *
 * Class Domain_File_Scenario_Api
 */
abstract class Domain_File_Scenario_Api {

	protected const _API_VERSION = 1;

	/**
	 * получить url файловой ноды
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
	public static function getUrl(string $token):string {

		// достаём бота из кэша
		$userbot = Gateway_Bus_UserbotCache::get($token);
		Domain_Userbot_Entity_Userbot::assertUserbotEnabled($userbot);

		$status = Domain_Request_Entity_Request::STATUS_FAILED;

		try {

			[$node_url, $file_token] = Gateway_Socket_FileBalancer::getNodeUrl($userbot->userbot_user_id, $userbot->domino_entrypoint, $userbot->company_id);

			$status      = Domain_Request_Entity_Request::STATUS_SUCCESS;
			$result_data = self::_formatFileNodeInfo($node_url, $file_token, static::_API_VERSION);
		} catch (\cs_Userbot_RequestFailed | \Exception | \Error) {

			// неизвестная ошибка при выполнении запроса
			$result_data = Domain_Request_Entity_Request::getErrorResponse(Domain_Request_Entity_Request::ACTION_REQUEST, CASE_EXCEPTION_CODE_6);
		}

		// запрос выполнился синхронно, теперь нужно сохранить результаты в таблицу request_list
		// чтобы пользователь мог получить их по переданному request_id
		$request_data = [
			"request_type"    => Domain_Request_Entity_Request::ACTION_GET_FILE_NODE_URL,
			"request_version" => static::_API_VERSION,
			"userbot_user_id" => $userbot->userbot_user_id,
			"company_url"     => $userbot->company_url,
		];
		$request = Domain_Userbot_Action_AddRequest::do($token, $request_data, $result_data, $status);

		// возвращаем в ответе id запроса
		return $request->request_id;
	}

	/**
	 * приводим к формату данные файловой ноды
	 */
	protected static function _formatFileNodeInfo(string $node_url, string $file_token, int $request_version = 1):array {

		return match ($request_version) {
			default => Apiv1_Format::fileNodeInfo($node_url, $file_token),
			2 => Apiv2_Format::fileNodeInfo($node_url, $file_token),
		};
	}
}