<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с конфиг-файлом аутентификации api/conf/ldap.php
 * @package Compass\Pivot
 */
class Domain_User_Entity_Ldap_Config
{
	/** ключ для получения конфига с параметрами LDAP */
	protected const _KEY_LDAP = "LDAP";

	/**
	 * Требуется ли обновлять значение атрибутов, которые не заполнены для пользователя в LDAP
	 */
	public static function isEmptyAttributesUpdateEnabled(): bool
	{

		return self::_getConfig(self::_KEY_LDAP)["empty_attributes_update_enabled"];
	}

	/**
	 * получаем контент конфига
	 *
	 * @throws ParseFatalException
	 */
	protected static function _getConfig(string $config_key): array
	{

		$config = getConfig($config_key);

		// если пришел пустой конфиг
		if (count($config) < 1) {
			throw new ParseFatalException("unexpected content");
		}

		return $config;
	}
}
