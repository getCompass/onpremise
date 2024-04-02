<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с конфигом SSO провайдера
 * @package Compass\Federation
 */
class Domain_Sso_Entity_Oidc_Config {

	/** ключи под которыми хранятся конфиги api/conf/sso.php */
	protected const _KEY_CONNECTION          = "SSO_OIDC_CONNECTION";
	protected const _KEY_PROVIDER_CONFIG     = "SSO_OIDC_PROVIDER_CONFIG";
	protected const _KEY_ATTRIBUTION_MAPPING = "SSO_ATTRIBUTION_MAPPING";

	/** список полей/атрибутов, которые маппятся в конфиге SSO_ATTRIBUTION_MAPPING */
	public const MAPPED_ATTRIBUTE_FIRST_NAME   = "first_name";
	public const MAPPED_ATTRIBUTE_LAST_NAME    = "last_name";
	public const MAPPED_ATTRIBUTE_MAIL         = "mail";
	public const MAPPED_ATTRIBUTE_PHONE_NUMBER = "phone_number";

	/**
	 * Получаем client_id
	 *
	 * @return string
	 */
	public static function getClientID():string {

		$config = getConfig(self::_KEY_CONNECTION);
		return $config["client_id"];
	}

	/**
	 * Получаем client_secret
	 *
	 * @return string
	 */
	public static function getClientSecret():string {

		$config = getConfig(self::_KEY_CONNECTION);
		return $config["client_secret"];
	}

	/**
	 * Получаем конфиг SSO провайдера
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function getProviderConfig():array {

		$config = getConfig(self::_KEY_PROVIDER_CONFIG);

		if (count($config) < 1) {
			throw new ParseFatalException("provider config is not provided");
		}

		return $config;
	}

	/**
	 * Получаем оригинальное название атрибута аккаунта в SSO провайдере
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public static function getMappedAttributeName(string $attribute_name):string {

		$config = getConfig(self::_KEY_ATTRIBUTION_MAPPING);

		if (!isset($config[$attribute_name])) {
			throw new ParseFatalException("no mapping for attribute: $attribute_name");
		}

		return $config[$attribute_name];
	}
}