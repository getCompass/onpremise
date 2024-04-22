<?php declare(strict_types = 1);

namespace Compass\Premise;
/**
 * Структура для токена аутентификации
 */
class Struct_Premise_AuthToken {

	/**
	 * Конструктор
	 *
	 * @param string $authentication_token
	 * @param int    $need_refresh_at
	 */
	public function __construct(
		public string $authentication_token,
		public int    $need_refresh_at,
	) {
	}
}
