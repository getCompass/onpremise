<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * класс для работы с go_activity
 */
class Gateway_Bus_Activity {

	/**
	 * Получить информацию об активности пользователя
	 *
	 * @param int $user_id
	 *
	 * @return Struct_Db_PivotUser_UserActivityList
	 * @throws BusFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_UserNotFound
	 */
	public static function getUserOnline(int $user_id):Struct_Db_PivotUser_UserActivityList {

		$request = new \ActivityGrpc\UserGetActivityRequestStruct([
			"user_id" => $user_id,
		]);
		[$response, $status] = self::_doCallGrpc("UserGetActivity", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			if ($status->code == 901) {
				throw new cs_UserNotFound();
			}
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		// формируем структуру
		return self::_makeUserStruct($response);
	}

	/**
	 * Получить информацию об активности списка пользователей
	 *
	 * @param array $user_id_list
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getUserOnlineList(array $user_id_list):array {

		$request = new \ActivityGrpc\UserGetActivityListRequestStruct([
			"user_id_list" => $user_id_list,
		]);
		[$response, $status] = self::_doCallGrpc("UserGetActivityList", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		return self::_makeUserListStruct($response);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Форматируем информацию об активности пользователя
	 */
	protected static function _makeUserStruct(\ActivityGrpc\UserGetActivityResponseStruct $response):Struct_Db_PivotUser_UserActivityList {

		// формируем ответ
		return new Struct_Db_PivotUser_UserActivityList(
			$response->getUserId(),
			$response->getStatus(),
			$response->getCreatedAt(),
			$response->getUpdatedAt(),
			$response->getLastWsPingAt(),
		);
	}

	/**
	 * Форматируем информацию об активности пользователя
	 */
	protected static function _makeUserListStruct(\ActivityGrpc\UserGetActivityListResponseStruct $response):array {

		$user_online_list = [];
		foreach ($response->getActivityList() as $user_online) {

			$user_online_list[] = new Struct_Db_PivotUser_UserActivityList(
				$user_online->getUserId(),
				$user_online->getStatus(),
				$user_online->getCreatedAt(),
				$user_online->getUpdatedAt(),
				$user_online->getLastWsPingAt(),
			);
		}

		return $user_online_list;
	}

	/**
	 * делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @param string                            $method_name
	 * @param \Google\Protobuf\Internal\Message $request
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		$connection = ShardingGateway::rpc("activity", \ActivityGrpc\activityClient::class);

		return $connection->callGrpc($method_name, $request);
	}
}
