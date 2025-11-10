<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * Выполняет генерацию токена авторизации для указанного пользователя.
 */
class Domain_Solution_Action_GenerateAuthenticationToken {

	protected const _TOKEN_TTL = 30 * 60;

	/**
	 * Генерирует токен авторизации для указанного пользователя.
	 */
	public static function exec(int $user_id, string|false $join_link_uniq = false, ?int $login_type = null):array {

		// генерируем токен и ключ
		/** @var Struct_Solution_AuthenticationKeyCache|false $authentication_key_obj */
		$authentication_key_obj = ShardingGateway::cache()->get(static::makeKey($user_id));

		if ($authentication_key_obj !== false && $authentication_key_obj->expires_at > time()) {

			$expires_at = $authentication_key_obj->expires_at;
			$token      = Domain_Solution_Entity_AuthenticationToken::generate($user_id, $expires_at, $authentication_key_obj->authentication_key, $join_link_uniq);
			return [$token, $expires_at];
		}

		$authentication_key     = generateUUID();
		$expires_at             = time() + self::_TOKEN_TTL;
		$authentication_key_obj = new Struct_Solution_AuthenticationKeyCache(
			$authentication_key,
			$expires_at,
			$login_type,
		);

		ShardingGateway::cache()->set(static::makeKey($user_id), $authentication_key_obj, self::_TOKEN_TTL);

		$token = Domain_Solution_Entity_AuthenticationToken::generate($user_id, $expires_at, $authentication_key, $join_link_uniq);

		return [$token, $expires_at];
	}

	/**
	 * Возвращает ключ для временного хранения токена аутентификации.
	 */
	public static function makeKey(int $user_id):string {

		return "authentication_key_$user_id";
	}
}
