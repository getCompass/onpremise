<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Класс отправки данных в go-userbot-cache.
 */
class Gateway_Bus_UserbotCache {

	/**
	 * Получить инфу о боте по токену
	 *
	 * @throws \busException
	 * @throws \cs_Userbot_NotFound
	 * @throws \userAccessException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public static function get(string $token):Struct_Userbot_Info {

		if (isset($GLOBALS["userbot_list"][$token])) {
			return $GLOBALS["userbot_list"][$token];
		}

		$request = new \UserbotCacheGrpc\UserbotGetOneRequestStruct([
			"token" => $token,
		]);
		/** @noinspection \PhpParamsInspection */
		[$response, $status] = self::_doCallGrpc("UserbotGetOne", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			if ($status->code == 401) {
				throw new EndpointAccessDeniedException("error get userbot");
			}

			if ($status->code == 901) {
				throw new \cs_Userbot_NotFound();
			}
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		// формируем структуру
		$userbot_info = self::_makeUserStruct($response);
		if ($userbot_info->userbot_user_id == 0) {
			throw new \cs_Userbot_NotFound();
		}

		$GLOBALS["userbot_list"][$token] = $userbot_info;
		return $userbot_info;
	}

	/**
	 * Готовим структуру данных с информацией бота
	 *
	 */
	protected static function _makeUserStruct(\UserbotCacheGrpc\UserbotGetOneResponseStruct $response):Struct_Userbot_Info {

		return new Struct_Userbot_Info(
			$response->getUserbotId(),
			$response->getToken(),
			$response->getStatus(),
			$response->getCompanyUrl(),
			$response->getCompanyId(),
			$response->getDominoEntrypoint(),
			$response->getSecretKey(),
			$response->getIsReactCommand(),
			$response->getUserbotUserId(),
			fromJson($response->getExtra()),
		);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Вызов GRPC метода.
	 *
	 * @noinspection PhpUndefinedClassInspection
	 * @noinspection \PhpUndefinedNamespaceInspection
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws busException
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		$connection = ShardingGateway::rpc("userbot_cache", \UserbotCacheGrpc\userbotCacheClient::class);

		[$response, $status] = $connection->callGrpc($method_name, $request);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		return [$response, $status];
	}
}