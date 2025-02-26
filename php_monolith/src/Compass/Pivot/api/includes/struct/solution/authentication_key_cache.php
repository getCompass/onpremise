<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * Структура ключа аутентификации для кеша
 */
class Struct_Solution_AuthenticationKeyCache {

	/**
	 * Токен аутентификации.
	 */
	public function __construct(
		public string   $authentication_key,
		public int      $expires_at,
		public int|null $login_type
	) {

	}
}
