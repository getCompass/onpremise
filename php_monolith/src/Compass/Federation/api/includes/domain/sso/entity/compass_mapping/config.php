<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с конфигом $CONFIG["SSO_COMPASS_MAPPING"]
 * @package Compass\Federation
 */
class Domain_Sso_Entity_CompassMapping_Config {

	/** ключ под которым хранится конфиг */
	protected const _CONFIG_KEY = "SSO_COMPASS_MAPPING";

	/** список полей, которые маппятся из SSO провайдера */
	public const MAPPED_FIELD_NAME   = "name";
	public const MAPPED_FIELD_AVATAR = "avatar";
	public const MAPPED_FIELD_BADGE  = "badge";
	public const MAPPED_FIELD_ROLE   = "role";
	public const MAPPED_FIELD_BIO    = "bio";

	/**
	 * Получаем содержимое sso.compass_mapping.$field_name из конфига $CONFIG["SSO_COMPASS_MAPPING"]
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public static function getMappedFieldContent(string $field_name):string {

		$config = getConfig(self::_CONFIG_KEY);

		if (!isset($config[$field_name])) {
			throw new ParseFatalException("no mapping for field: $field_name");
		}

		return $config[$field_name];
	}
}