<?php declare(strict_types=1);

namespace Compass\Pivot;

/**
 * Удаляет данные ключа аутентификации.
 */
class Domain_Solution_Action_InvalidateAuthenticationKey {

	/**
	 * Генерирует токен авторизации для указанного пользователя.
	 */
	public static function exec(string $token_cache_key):void {

		// удаляем токен, чтобы им нельзя было воспользоваться
		ShardingGateway::cache()->delete($token_cache_key);
	}
}
