<?php declare(strict_types = 1);

namespace Compass\Pivot;

use BaseFrame\Crypt\CryptProvider;

/**
 * Класс для работы с токенами аутентификации.
 * Токен используется для аутентификации в приложении после успешной аутентификации на сайте.
 */
class Domain_Solution_Entity_AuthenticationToken {

	protected const _PAYLOAD_VERSION = 1;

	/**
	 * Генерирует токен аутентификации.
	 */
	public static function generate(int $user_id, int $expires_at, string $authentication_key, string|false $join_link_uniq = false):string {

		$payload = static::_makePayload($user_id, $expires_at, $authentication_key, $join_link_uniq);
		$sign    = static::_singPayload($payload, Gateway_Lic_OnPremise::getSignKey());

		return \BaseFrame\String\Base58::encode(toJson([
			"version" => static::_PAYLOAD_VERSION,
			"payload" => $payload,
			"sign"    => $sign,
		]));
	}

	/**
	 * Раскодирует переданный токен и расшифровывает ключ.
	 * @throws Domain_Solution_Exception_BadAuthenticationToken
	 */
	public static function decrypt(string $token):Struct_Solution_AuthenticationToken {

		// извлекаем полезную нагрузку из токена
		$payload = static::_extractPayload($token);

		// расшифровываем
		$iv_length      = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv             = substr(CryptProvider::default()->vector(), 0, $iv_length);
		$decrypt_result = openssl_decrypt(
			$payload["authentication_key"], ENCRYPT_CIPHER_METHOD, CryptProvider::default()->key(), 0, $iv
		);

		if ($decrypt_result === false) {
			throw new Domain_Solution_Exception_BadAuthenticationToken("bad authentication key");
		}

		return new Struct_Solution_AuthenticationToken(
			$payload["user_id"],
			$payload["expires_at"],
			$payload["server_uid"],
			$payload["start_endpoint_url"],
			$payload["pivot_domain"],
			$payload["protocol"],
			$payload["join_link_uniq"] ?? "",
			$decrypt_result,
		);
	}

	/**
	 * Генерирует массив с полезной нагрузкой токена.
	 */
	protected static function _makePayload(int $user_id, int $expires_at, string $authentication_key, string|false $join_link_uniq = false):array {

		$iv_length = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv        = substr(CryptProvider::default()->vector(), 0, $iv_length);

		// шифруем ключ
		$encrypted_authentication_key = openssl_encrypt(
			$authentication_key, ENCRYPT_CIPHER_METHOD, CryptProvider::default()->key(), 0, $iv
		);

		return [
			"server_uid"         => SERVER_UID,
			"start_endpoint_url" => PUBLIC_ENTRYPOINT_START . "/",
			"pivot_domain"       => substr(PUBLIC_ENTRYPOINT_PIVOT, strlen(WEB_PROTOCOL_PUBLIC . "://")),
			"pivot_url"          => PUBLIC_ENTRYPOINT_PIVOT,
			"protocol"           => WEB_PROTOCOL_PUBLIC,
			"user_id"            => $user_id,
			"expires_at"         => $expires_at,
			"join_link_uniq"     => $join_link_uniq !== false ? $join_link_uniq : "",
			"authentication_key" => $encrypted_authentication_key,
		];
	}

	/**
	 * Подписывает полезную нагрузку токена.
	 */
	protected static function _singPayload(array $payload, string $sign_key):string {

		ksort($payload);
		return md5(hash_hmac("sha1", toJson($payload), $sign_key));
	}

	/**
	 * Извлекает полезную нагрузку из токена аутентификации.
	 * @throws Domain_Solution_Exception_BadAuthenticationToken
	 */
	protected static function _extractPayload(string $token):array {

		$json_token = \BaseFrame\String\Base58::decode($token);

		if ($json_token === false) {
			throw new Domain_Solution_Exception_BadAuthenticationToken("can't decode token");
		}

		$token_data = fromJson($json_token);

		if (!isset($token_data["payload"])) {
			throw new Domain_Solution_Exception_BadAuthenticationToken("incorrect token");
		}

		$sing = static::_singPayload($token_data["payload"], Gateway_Lic_OnPremise::getSignKey());

		if ($sing !== $token_data["sign"]) {
			throw new Domain_Solution_Exception_BadAuthenticationToken("bad sign");
		}

		$payload = $token_data["payload"];

		if (!isset($payload["authentication_key"])) {
			throw new Domain_Solution_Exception_BadAuthenticationToken("authentication key not found");
		}

		return $payload;
	}
}