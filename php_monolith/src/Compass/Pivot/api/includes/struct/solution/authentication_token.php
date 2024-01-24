<?php declare(strict_types=1);

namespace Compass\Pivot;

/**
 * Структура токена аутентификаиции для входа в учетную запись.
 */
class Struct_Solution_AuthenticationToken {

	/**
	 * Токен аутентификации.
	 */
	public function __construct(
		public int    $user_id,
		public int    $expires_at,
		public string $server_uid,
		public string $start_endpoint_url,
		public string $pivot_domain,
		public string $protocol,
		public string $join_link_uniq,
		public string $authentication_key,
	) {

	}
}
