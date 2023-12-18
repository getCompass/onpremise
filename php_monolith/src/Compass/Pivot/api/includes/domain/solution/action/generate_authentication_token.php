<?php declare(strict_types=1);

namespace Compass\Pivot;

/**
 * Выполняет генерацию токена авторизации для указанного пользователя.
 */
class Domain_Solution_Action_GenerateAuthenticationToken {

	protected const _TOKEN_TTL = 10 * 60;

	/**
	 * Генерирует токен авторизации для указанного пользователя.
	 */
	public static function exec(int $user_id, int|false $expires_at = false, string|false $join_link_uniq = false):string {

		// генерируем токен и ключ
		$authentication_key = generateUUID();
		$expires_at         = $expires_at ?: time() + static::_TOKEN_TTL;

		$token = Domain_Solution_Entity_AuthenticationToken::generate($user_id, $expires_at, $authentication_key, $join_link_uniq);

		if ($expires_at > time()) {

			// TODO: Memcache — решение для MVP, нужно что-то более основательное
			ShardingGateway::cache()->set(static::makeKey($user_id), $authentication_key, max(0, $expires_at - time()));
		}

		return $token;
	}

	/**
	 * Возвращает ключ для временного хранения токена аутентификации.
	 */
	public static function makeKey(int $user_id):string {

		return "authentication_key_$user_id";
	}
}
