<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Класс для валидации токена аутентификации
 * @package Compass\Pivot
 */
class Domain_Solution_Entity_AuthenticationValidator {

	/**
	 * Валидируем токен
	 *
	 * @return Struct_Solution_AuthenticationToken
	 * @throws Domain_Solution_Exception_BadAuthenticationToken
	 * @throws Domain_Solution_Exception_ExpiredAuthenticationToken
	 */
	public static function validate(string $authentication_token):Struct_Solution_AuthenticationToken {

		// пытаемся расшифровать токен
		$authentication_token_data = Domain_Solution_Entity_AuthenticationToken::decrypt($authentication_token);

		// если это on-premise и токен находится в списке доверенных
		if (ServerProvider::isOnPremise() && self::_isTrustedToken($authentication_token)) {
			return $authentication_token_data;
		}

		$token_cache_key = Domain_Solution_Action_GenerateAuthenticationToken::makeKey($authentication_token_data->user_id);
		$last_active_key = ShardingGateway::cache()->get($token_cache_key);

		if ($last_active_key !== $authentication_token_data->authentication_key) {
			throw new Domain_Solution_Exception_BadAuthenticationToken("bad token data");
		}

		if ($authentication_token_data->expires_at <= time()) {
			throw new Domain_Solution_Exception_ExpiredAuthenticationToken("token expired");
		}

		return $authentication_token_data;
	}

	/**
	 * Проверяем, находится ли токен в списке доверенных
	 * Токены из этого списка всегда с успехом проходят валидацию
	 *
	 * @return bool
	 */
	protected static function _isTrustedToken(string $authentication_token):bool {

		return in_array($authentication_token, TRUSTED_AUTH_TOKEN_LIST);
	}
}