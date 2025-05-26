<?php declare(strict_types = 1);

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для работы с токенами аутентификации.
 * Токен используется для аутентификации в smart app.
 */
class Domain_SmartApp_Entity_AuthenticationToken {

	protected const _PAYLOAD_VERSION = 1;

	/**
	 * Генерирует токен аутентификации.
	 */
	public static function generate(int    $user_id, string $userbot_title,
						  string $smart_app_name, string $smart_app_avatar_url, string $smart_app_private_key,
						  int    $client_width, int $client_height, string $platform,
						  string $entity, string $entity_key):string {

		$issued_at    = time();
		$expires_at   = $issued_at + (60 * 30);
		$sign_payload = static::_makeSignPayload($user_id, $userbot_title, $smart_app_name, $smart_app_avatar_url, $entity, $entity_key, $expires_at, $issued_at);
		$app_data     = static::_makeAppData($client_width, $client_height, $platform);
		$sign         = static::_singPayload($sign_payload, $smart_app_private_key);

		return \BaseFrame\String\Base58::encode(toJson([
			"sign"         => $sign,
			"version"      => static::_PAYLOAD_VERSION,
			"sign_payload" => $sign_payload,
			"app_data"     => $app_data,
		]));
	}

	/**
	 * Генерирует массив с полезной нагрузкой токена.
	 */
	protected static function _makeSignPayload(int    $user_id, string $userbot_title,
								 string $smart_app_name, string $smart_app_avatar_url,
								 string $entity, string $entity_key,
								 int    $expires_at, int $issued_at):array {

		return [
			"user_data"      => [
				"user_id" => $user_id,
			],
			"smart_app_data" => [
				"title"      => $userbot_title,
				"name"       => $smart_app_name,
				"avatar_url" => $smart_app_avatar_url,
				"entity"     => $entity,
				"entity_key" => $entity_key,
			],
			"expires_at"     => $expires_at,
			"issued_at"      => $issued_at,
			"nonce"          => bin2hex(random_bytes(16)),
		];
	}

	/**
	 * Генерирует массив с информацией о приложении
	 */
	protected static function _makeAppData(int $client_width, int $client_height, string $platform):array {

		return [
			"client_width"  => $client_width,
			"client_height" => $client_height,
			"platform"      => $platform,
		];
	}

	/**
	 * Подписывает полезную нагрузку токена.
	 */
	protected static function _singPayload(array $payload, string $private_key):string {

		ksort($payload);
		$payloadJson = json_encode($payload);

		$pkey_id = openssl_get_privatekey($private_key);
		if (!$pkey_id) {
			throw new ReturnFatalException("incorrect private key");
		}

		$signature = "";
		$result    = openssl_sign($payloadJson, $signature, $pkey_id, OPENSSL_ALGO_SHA256);
		if (!$result) {
			throw new ReturnFatalException("failed to generate payload sign");
		}

		return base64_encode($signature);
	}

	/**
	 * Валидирует токен, используя публичный ключ.
	 *
	 * @param string $token
	 * @param string $public_key
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public static function validate(string $token, string $public_key):bool {

		$decodedTokenJson = \BaseFrame\String\Base58::decode($token);
		$data             = fromJson($decodedTokenJson);

		if (!isset($data["payload"]) || !isset($data["sign"])) {
			return false;
		}

		$payload     = $data["payload"];
		$signature   = base64_decode($data["sign"]);
		$payloadJson = json_encode($payload);

		$pkey_id = openssl_get_publickey($public_key);
		if (!$pkey_id) {
			throw new \Exception("Невозможно получить публичный ключ");
		}

		$result = openssl_verify($payloadJson, $signature, $pkey_id, OPENSSL_ALGO_SHA256);
		if ($result !== 1) {
			return false;
		}

		// проверка срока действия токена
		if (time() > $payload["expires_at"] || time() < $payload["issued_at"]) {
			return false;
		}

		return true;
	}
}