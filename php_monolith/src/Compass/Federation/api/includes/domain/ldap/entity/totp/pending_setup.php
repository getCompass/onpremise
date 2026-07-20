<?php

namespace Compass\Federation;

/**
 * Управляет временным (незавершенным) состоянием настройки TOTP - хранится в кэше
 * Создается при первой попытке авторизации без привязанного TOTP
 * Удаляется после успешного подтверждения кода
 *
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_Totp_PendingSetup
{
	/** TTL кэша в секундах (5 минут) */
	public const TTL_SEC = 60 * 5;

	/** префикс ключа кэша */
	private const _CACHE_KEY_PREFIX = "ldap_totp_pending_setup:";

	public string $crypted_totp_secret;

	public int $expires_at;

	protected function __construct()
	{
	}

	/**
	 * Создать новую запись незавершенной настройки TOTP для пользователя
	 */
	public static function create(string $uid): self
	{

		$totp_secret         = Domain_Totp_Entity_Generator::generateSecret();
		$crypted_totp_secret = \BaseFrame\Crypt\CrypterProvider::get(Domain_Ldap_Entity_Totp_UserRel::TOTP_SECRET_CRYPT_KEY)->encrypt($totp_secret);
		$expires_at          = time() + self::TTL_SEC;

		$pending                      = new self();
		$pending->crypted_totp_secret = $crypted_totp_secret;
		$pending->expires_at          = $expires_at;

		ShardingGateway::cache()->set(self::_makeCacheKey($uid), [
			"crypted_totp_secret" => $crypted_totp_secret,
			"expires_at"          => $expires_at,
		], self::TTL_SEC);

		return $pending;
	}

	/**
	 * Получить запись незавершенной настройки из кэша; вернет false если не найдена или просрочена
	 */
	public static function find(string $uid): self | false
	{

		$cached = ShardingGateway::cache()->get(self::_makeCacheKey($uid));
		if (!$cached) {
			return false;
		}

		$pending                      = new self();
		$pending->crypted_totp_secret = $cached["crypted_totp_secret"];
		$pending->expires_at          = $cached["expires_at"];

		return $pending;
	}

	/**
	 * Удалить запись незавершенной настройки из кэша
	 */
	public static function delete(string $uid): void
	{

		ShardingGateway::cache()->delete(self::_makeCacheKey($uid));
	}

	/**
	 * Сформировать ключ кэша по uid пользователя
	 */
	private static function _makeCacheKey(string $uid): string
	{

		return self::_CACHE_KEY_PREFIX . $uid;
	}
}
