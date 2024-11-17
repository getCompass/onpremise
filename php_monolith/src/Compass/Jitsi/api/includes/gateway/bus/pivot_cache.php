<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * класс для работы с go_pivot_cache
 */
class Gateway_Bus_PivotCache {

	// -------------------------------------------------------
	// Session
	// -------------------------------------------------------

	// получить информацию по сессии
	public static function getInfo(string $session_uniq, string $shard_id, int $table_id):array {

		// если нашли сессию в GLOBALS - возвращаем
		$session = self::_getSessionFromGlobals($session_uniq);
		if ($session !== false) {
			return $session;
		}

		$request = new \PivotCacheGrpc\SessionGetInfoRequestStruct([
			"session_uniq" => $session_uniq,
			"shard_id"     => $shard_id,
			"table_id"     => $table_id,
		]);

		/** @var \PivotCacheGrpc\SessionGetInfoResponseStruct $response */
		[$response, $status] = self::_doCallGrpc("SessionGetInfo", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			// если go_pivot_cache не смог получить данные из БД
			if ($status->code == 500) {
				throw new BusFatalException("database error in go_pivot_cache");
			}

			// если сессия не найдена
			if ($status->code == 902) {
				throw new \cs_SessionNotFound();
			}

			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		// записываем сессию в GLOBALS
		$GLOBALS["session_list"][$session_uniq] = [
			"user_id"      => $response->getUserId(),
			"refreshed_at" => $response->getRefreshedAt(),
		];

		return $GLOBALS["session_list"][$session_uniq];
	}

	/**
	 * Попытаться получить сессию из GLOBALS
	 *
	 * @param string $session_uniq
	 *
	 * @return array|false
	 */
	protected static function _getSessionFromGlobals(string $session_uniq):array|false {

		//
		if (isset($GLOBALS["session_list"][$session_uniq])) {

			// если рефреша еще нет в GLOBALS - добавляем 0, так как никогда не обновляли куку значит
			if (is_int($GLOBALS["session_list"][$session_uniq])) {

				$GLOBALS["session_list"][$session_uniq] = [
					"user_id"      => $GLOBALS["session_list"][$session_uniq],
					"refreshed_at" => 0,
				];
			}
			return $GLOBALS["session_list"][$session_uniq];
		}

		return false;
	}

	// -------------------------------------------------------
	// User
	// -------------------------------------------------------

	/**
	 * Получить инфу о пользователе по user_id
	 *
	 * @param int $user_id
	 *
	 * @return Struct_Db_PivotUser_User
	 * @throws BusFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_UserNotFound
	 */
	public static function getUserInfo(int $user_id):Struct_Db_PivotUser_User {

		if (isset($GLOBALS["user_list"][$user_id])) {
			return $GLOBALS["user_list"][$user_id];
		}

		$request = new \PivotCacheGrpc\UsersGetInfoRequestStruct([
			"user_id" => $user_id,
		]);
		[$response, $status] = self::_doCallGrpc("UserGetInfo", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			if ($status->code == 901) {
				throw new cs_UserNotFound();
			}
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		// формируем структуру
		$user_info = self::_makeUserStruct($response);

		$GLOBALS["user_list"][$user_id] = $user_info;
		return $user_info;
	}

	/**
	 * Получить инфу о пользователях по id
	 *
	 * @param array $user_id_list
	 *
	 * @return Struct_Db_PivotUser_User[]
	 *
	 * @throws BusFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_UserNotFound
	 */
	public static function getUserListInfo(array $user_id_list):array {

		$need_user_id_list = [];
		foreach ($user_id_list as $user_id) {

			if ($user_id != 0) {
				$need_user_id_list[] = $user_id;
			}
		}
		if (count($need_user_id_list) < 1) {
			return [];
		}

		$request = new \PivotCacheGrpc\UsersGetInfoListRequestStruct([
			"user_id_list" => $need_user_id_list,
		]);
		[$response, $status] = self::_doCallGrpc("UserGetInfoList", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			throw match ($status->code) {

				903     => new cs_UserNotFound(),
				default => new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code),
			};
		}

		// формируем структуру
		return self::_makeUserListStruct($response);
	}

	/**
	 * Форматируем информацию о пользователе
	 *
	 */
	protected static function _makeUserStruct(\PivotCacheGrpc\UsersGetInfoResponseStruct $response):Struct_Db_PivotUser_User {

		// формируем ответ
		return new Struct_Db_PivotUser_User(
			$response->getUserId(),
			$response->getNpcType(),
			$response->getInvitedByPartnerId(),
			$response->getInvitedByUserId(),
			$response->getLastActiveDayStartAt(),
			$response->getCreatedAt(),
			$response->getUpdatedAt(),
			$response->getFullNameUpdatedAt(),
			$response->getCountryCode(),
			$response->getFullName(),
			$response->getAvatarFileMap(),
			fromJson($response->getExtra())
		);
	}

	/**
	 * Форматируем информацию о списке пользователей
	 *
	 * @return Struct_Db_PivotUser_User[]
	 */
	protected static function _makeUserListStruct(\PivotCacheGrpc\UsersGetInfoListResponseStruct $response):array {

		$user_info_list = [];

		$user_list = $response->getUserList();
		foreach ($user_list as $user) {

			// формируем ответ
			$user_info_list[$user->getUserId()] = new Struct_Db_PivotUser_User(
				$user->getUserId(),
				$user->getNpcType(),
				$user->getInvitedByPartnerId(),
				$user->getInvitedByUserId(),
				$user->getLastActiveDayStartAt(),
				$user->getCreatedAt(),
				$user->getUpdatedAt(),
				$user->getFullnameUpdatedAt(),
				$user->getCountryCode(),
				$user->getFullName(),
				$user->getAvatarFileMap(),
				fromJson($user->getExtra())
			);
		}

		return $user_info_list;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

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

		$connection = ShardingGateway::rpc("pivot_cache", \PivotCacheGrpc\pivotCacheClient::class);

		return $connection->callGrpc($method_name, $request);
	}
}
