<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * Класс для уровневых блокировок по IP
 */
class Type_Antispam_Leveled_Ip extends Type_Antispam_Leveled_Main {

	public const START_AUTH = [
		"key"   => "AUTH",
		"level" => [
			self::LIGHT_LEVEL   => [
				"limit"  => 5,
				"expire" => HOUR1,
			],
			self::MEDIUM_LEVEL  => [
				"limit"  => 3,
				"expire" => HOUR1,
			],
			self::WARNING_LEVEL => [
				"limit"  => -1,
				"expire" => HOUR1,
			],
		],
	];

	/** лимиты для начала аутентификации на онпремайза */
	protected const _ONPREMISE_START_AUTH = [
		"key"   => "AUTH",
		"level" => [
			self::LIGHT_LEVEL   => [
				"limit"  => 4,
				"expire" => HOUR1,
			],
			self::MEDIUM_LEVEL  => [
				"limit"  => 2,
				"expire" => HOUR1,
			],
			self::WARNING_LEVEL => [
				"limit"  => -1,
				"expire" => HOUR1,
			],
		],
	];

	/** лимиты для ввода пароля при аутентификации через почту на онпремайза */
	protected const _ONPREMISE_ENTER_PASSWORD = [
		"key"   => "ENTER_PASSWORD",
		"level" => [
			self::LIGHT_LEVEL   => [
				"limit"  => 2,
				"expire" => HOUR1,
			],
			self::MEDIUM_LEVEL  => [
				"limit"  => 2,
				"expire" => HOUR1,
			],
			self::WARNING_LEVEL => [
				"limit"  => 2,
				"expire" => HOUR1,
			],
		],
	];

	/**
	 * получаем лимиты аутентификации в зависимости от сервера
	 */
	public static function getAuthLimitsByServer():array {

		if (ServerProvider::isOnPremise()) {
			return self::_prepareOnpremiseAuthLimits(self::_ONPREMISE_START_AUTH);
		}

		return self::START_AUTH;
	}

	/**
	 * получаем лимиты аутентификации в зависимости от сервера
	 */
	public static function getEnterPasswordLimitsByServer():array {

		if (ServerProvider::isOnPremise()) {
			return self::_prepareOnpremiseAuthLimits(self::_ONPREMISE_ENTER_PASSWORD);
		}

		throw new ParseFatalException("unexpected behaviour");
	}

	/**
	 * подготавливаем лимиты аутентификации для on-premise решения
	 *
	 * @return array
	 */
	protected static function _prepareOnpremiseAuthLimits(array $block_key):array {

		$output = $block_key;

		// устанавливаем лимит из конфигурации для каждого уровня блокировки
		// поскольку на onpremise решении многоуровневая блокировка не предусмотрена
		$limit = Domain_User_Entity_Auth_Config::getCaptchaRequireAfter();
		foreach ($output["level"] as $level => $item) {
			$output["level"][$level]["limit"] = $limit;
		}

		return $output;
	}
}