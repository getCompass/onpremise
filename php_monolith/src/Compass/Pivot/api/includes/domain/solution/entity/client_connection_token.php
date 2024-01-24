<?php declare(strict_types=1);

namespace Compass\Pivot;

/**
 * Класс для работы с токенами клиентских подключений.
 * Токен используется для проверки лицензии решения при подключении клиентского приложения.
 */
class Domain_Solution_Entity_ClientConnectionToken {

	protected const _PAYLOAD_VERSION = 1;
	protected const _TOKEN_TTL       = 10 * 60;

	/**
	 * Генерирует токен клиентского подключения.
	 */
	public static function generate(int $user_id, int|false $expires_at = false):string {

		$payload = static::_makePayload($user_id, $expires_at ?: time() + static::_TOKEN_TTL);
		$sign    = static::_singPayload($payload, Gateway_Lic_OnPremise::getSignKey());

		// TODO: Сделать алгоритм шифрования для токена, base64 только для первой версии
		// TODO: Менять можно свободно, клиенты не пытаются что-то сделать с токеном

		return base64_encode(toJson([
			"version" => static::_PAYLOAD_VERSION,
			"payload" => $payload,
			"sign"    => $sign,
		]));
	}

	/**
	 * Генерирует массив с полезной нагрузкой токена.
	 */
	protected static function _makePayload(int $user_id, int $expires_at):array {

		return [
			"server_uid" => SERVER_UID,
			"user_id"    => $user_id,
			"expires_at" => $expires_at,
			"token_uniq" => generateRandomString(),
		];
	}

	/**
	 * Подписывает полезную нагрузку токена.
	 */
	protected static function _singPayload(array $payload, string $sign_key):string {

		// TODO: Поменять подпись на привычные 6 символов вместо md5
		// TODO: Держать в синхронизации с сервером лицензий

		ksort($payload);
		return md5(hash_hmac("sha1", toJson($payload), $sign_key));
	}
}