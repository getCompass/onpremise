<?php

namespace Compass\Announcement;

/**
 * Класс для работы с пользователем
 */
class Type_Auth_Main {

	/**
	 * @var Type_Auth_User|null
	 */
	protected static ?Type_Auth_User $user = null;

	/**
	 * Получить текущего пользователя
	 *
	 * @return Type_Auth_User
	 */
	public static function getUser():Type_Auth_User {

		if (self::$user === null) {
			self::$user = new Type_Auth_User();
		}

		return self::$user;
	}

	/**
	 * Получить пользователя из токена
	 *
	 * @param string $token
	 * @param string $device_id
	 *
	 * @return void
	 */
	public static function initUserFromToken(string $token, string $device_id):void {

		$user_id = self::getUserIdByToken($token, $device_id);

		self::$user = new Type_Auth_User($user_id);
	}

	/**
	 * Получить id пользователя из токена
	 *
	 * @param string $token
	 * @param string $device_id
	 *
	 * @return int
	 */
	public static function getUserIdByToken(string $token, string $device_id):int {

		$payload = Type_Jwt_Main::getPayloadFromToken($token);

		$user_id = $payload["id"] ?? 0;

		if ($user_id === 0) {
			return 0;
		}

		try {

			$token_user = Gateway_Db_AnnouncementSecurity_TokenUser::get($user_id, $device_id);
		} catch (\cs_RowIsEmpty) {
			return 0;
		}

		if (!Type_Jwt_Main::validate($token, $token_user->token)) {
			return 0;
		}

		return $user_id;
	}
}