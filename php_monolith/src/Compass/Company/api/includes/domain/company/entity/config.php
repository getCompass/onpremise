<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Permission;

/**
 * Класс для валидации данных конфига
 */
class Domain_Company_Entity_Config {

	// -------------------------------------------------------
	// !!! - часть данных дублируются в php_world
	// -------------------------------------------------------

	public const PUSH_BODY_DISPLAY_KEY                       = "is_display_push_body";
	public const HIRE_AND_DISMISS_REQUEST_ALLOWED_TO_ALL_KEY = "is_hire_and_dismiss_request_allowed_to_all";
	public const MEMBER_COUNT                                = "member_count";
	public const GUEST_COUNT                                 = "guest_count";
	public const IS_PIN_REQUIRED                             = "is_pin_required";
	public const COMPANY_NAME                                = "company_name";
	public const COMPANY_CREATED_AT                          = "company_created_at";
	public const MODULE_EXTENDED_EMPLOYEE_CARD_KEY           = "module_extended_employee_card";
	public const POSTMODERATION                              = "postmoderation";
	public const GENERAL_CHAT_NOTIFICATIONS                  = "general_chat_notifications";
	public const PREMIUM_PAYMENT_REQUESTING_KEY              = "is_premium_payment_requesting_enabled";
	public const PERMISSIONS_VERSION                         = "permissions_version";
	public const ADD_TO_GENERAL_CHAT_ON_HIRING               = "is_add_to_general_chat_on_hiring";
	public const ROOT_USER_ID                                = "root_user_id";

	public const GENERAL_CONVERSATION_KEY_NAME     = "general_conversation_key";
	public const ACHIEVEMENT_CONVERSATION_KEY_NAME = "achievement_conversation_key";
	public const RESPECT_CONVERSATION_KEY_NAME     = "respect_conversation_key";

	protected const _CONFIG_ALLOWED_VALUES_LIST = [
		self::PUSH_BODY_DISPLAY_KEY                       => [0, 1],
		self::HIRE_AND_DISMISS_REQUEST_ALLOWED_TO_ALL_KEY => [0, 1],
		self::MEMBER_COUNT                                => "integer",
		self::GUEST_COUNT                                 => "integer",
		self::IS_PIN_REQUIRED                             => [0, 1],
		self::COMPANY_NAME                                => "string",
		self::COMPANY_CREATED_AT                          => "integer",
		self::MODULE_EXTENDED_EMPLOYEE_CARD_KEY           => [0, 1],
		self::POSTMODERATION                              => [0, 1],
		self::GENERAL_CHAT_NOTIFICATIONS                  => [0, 1],
		self::ADD_TO_GENERAL_CHAT_ON_HIRING               => [0, 1],
		self::PREMIUM_PAYMENT_REQUESTING_KEY              => [0, 1],
		self::PERMISSIONS_VERSION                         => "integer",
		Permission::IS_DOWNLOAD_VIDEO_ENABLED             => [0, 1],
		Permission::IS_REPOST_MESSAGE_ENABLED             => [0, 1],
		Permission::IS_VOICE_MESSAGE_ENABLED              => [0, 1],
		Permission::IS_SHOW_COMPANY_MEMBER_ENABLED        => [0, 1],
		Permission::IS_SET_MEMBER_PROFILE_ENABLED         => [0, 1],
		Permission::IS_SHOW_GROUP_MEMBER_ENABLED          => [0, 1],
		Permission::IS_GET_REACTION_LIST_ENABLED          => [0, 1],
		Permission::IS_ADD_SINGLE_ENABLED                 => [0, 1],
		Permission::IS_ADD_GROUP_ENABLED                  => [0, 1],
		Permission::IS_CALL_ENABLED                       => [0, 1],
		self::ROOT_USER_ID                                => "integer",
	];

	// дефолтные значение настроек, если не нашли в базе
	public const CONFIG_DEFAULT_VALUE_LIST = [
		self::POSTMODERATION                              => 1,
		self::IS_PIN_REQUIRED                             => 0,
		self::PUSH_BODY_DISPLAY_KEY                       => 0, // для безопасности, если по какой-то причине потеряли в базе конфиг
		self::GENERAL_CHAT_NOTIFICATIONS                  => 0,
		self::ADD_TO_GENERAL_CHAT_ON_HIRING               => 1,
		self::PREMIUM_PAYMENT_REQUESTING_KEY              => 1,
		self::HIRE_AND_DISMISS_REQUEST_ALLOWED_TO_ALL_KEY => 0,
		self::MODULE_EXTENDED_EMPLOYEE_CARD_KEY           => 0,
		self::MEMBER_COUNT                                => 0,
		self::GUEST_COUNT                                 => 0,
		self::PERMISSIONS_VERSION                         => 1,
		Permission::IS_DOWNLOAD_VIDEO_ENABLED             => 1,
		Permission::IS_REPOST_MESSAGE_ENABLED             => 1,
		Permission::IS_VOICE_MESSAGE_ENABLED              => 1,
		Permission::IS_SHOW_COMPANY_MEMBER_ENABLED        => 1,
		Permission::IS_SET_MEMBER_PROFILE_ENABLED         => 1,
		Permission::IS_SHOW_GROUP_MEMBER_ENABLED          => 1,
		Permission::IS_GET_REACTION_LIST_ENABLED          => 1,
		Permission::IS_ADD_SINGLE_ENABLED                 => 1,
		Permission::IS_ADD_GROUP_ENABLED                  => 1,
		Permission::IS_CALL_ENABLED                       => 1,
		self::ROOT_USER_ID                                => 0,
	];

	// значения настроек при создании компании
	public const CONFIG_ON_CREATE_VALUE_LIST = [
		self::POSTMODERATION                       => 0,
		self::PUSH_BODY_DISPLAY_KEY                => 1,
		self::MODULE_EXTENDED_EMPLOYEE_CARD_KEY    => 0,
		self::GENERAL_CHAT_NOTIFICATIONS           => 0,
		self::ADD_TO_GENERAL_CHAT_ON_HIRING        => 1,
		self::PREMIUM_PAYMENT_REQUESTING_KEY       => 1,
		self::PERMISSIONS_VERSION                  => 3,
		Permission::IS_DOWNLOAD_VIDEO_ENABLED      => 1,
		Permission::IS_REPOST_MESSAGE_ENABLED      => 1,
		Permission::IS_VOICE_MESSAGE_ENABLED       => 1,
		Permission::IS_SHOW_COMPANY_MEMBER_ENABLED => 1,
		Permission::IS_SET_MEMBER_PROFILE_ENABLED  => 1,
		Permission::IS_SHOW_GROUP_MEMBER_ENABLED   => 1,
		Permission::IS_GET_REACTION_LIST_ENABLED   => 1,
		Permission::IS_ADD_SINGLE_ENABLED          => 1,
		Permission::IS_ADD_GROUP_ENABLED           => 1,
		Permission::IS_CALL_ENABLED                => 1,
	];

	// переменные относящиеся к ограничениям пользователя
	public const MEMBER_PERMISSIONS_VALUE_LIST = [
		Permission::IS_DOWNLOAD_VIDEO_ENABLED,
		Permission::IS_REPOST_MESSAGE_ENABLED,
		Permission::IS_VOICE_MESSAGE_ENABLED,
		Permission::IS_SHOW_COMPANY_MEMBER_ENABLED,
		Permission::IS_SET_MEMBER_PROFILE_ENABLED,
		Permission::IS_SHOW_GROUP_MEMBER_ENABLED,
		Permission::IS_GET_REACTION_LIST_ENABLED,
		Permission::IS_ADD_SINGLE_ENABLED,
		Permission::IS_ADD_GROUP_ENABLED,
		Permission::IS_CALL_ENABLED,
	];

	/**
	 * Выбрасывает исключение когда значение невалидно
	 *
	 * @throws cs_InvalidConfigValue
	 */
	public static function assertValidConfigValue(string $key, mixed $value):void {

		if (!array_key_exists($key, self::_CONFIG_ALLOWED_VALUES_LIST)) {
			throw new cs_InvalidConfigValue();
		}

		// если это массив, проверяем, что значение есть в массиве разрешенных
		if (is_array(self::_CONFIG_ALLOWED_VALUES_LIST[$key])) {

			if (!in_array($value, self::_CONFIG_ALLOWED_VALUES_LIST[$key])) {
				throw new cs_InvalidConfigValue();
			}

			return;
		}

		// берем тип значения
		$value_type = gettype($value);

		// сверяем тип значения, если не подходит, выкидываем ошибку
		if ($value_type !== self::_CONFIG_ALLOWED_VALUES_LIST[$key]) {
			throw new cs_InvalidConfigValue();
		}
	}

	/**
	 * Изменить значение конфига
	 *
	 * @param int    $role
	 * @param int    $permissions
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws cs_InvalidConfigValue
	 * @throws \queryException
	 */
	public static function edit(int $role, int $permissions, string $key, mixed $value):bool {

		self::assertValidConfigValue($key, $value);

		Permission::assertCanEditSpaceSettings($role, $permissions);

		// достаём прошлое значение
		$config = self::get($key);

		// если значение не изменилось, то ничего не делаем
		if (isset($config["value"]) && $config["value"] === $value) {
			return false;
		}

		self::set($key, $value);

		return true;
	}

	/**
	 * Получить конфиг
	 *
	 * @param string $key
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function get(string $key):array {

		$config = Type_Company_Config::init()->get($key);

		// если мы получили пустой конфиг - устанавливаем значение для ключа по умолчанию
		if (count($config) < 1) {

			$time   = time();
			$value  = ["value" => Domain_Company_Entity_Config::CONFIG_DEFAULT_VALUE_LIST[$key]];
			$config = new Struct_Db_CompanyData_CompanyConfig($key, $time, $time, $value);
			Gateway_Db_CompanyData_CompanyConfig::insert($config);

			return $config->value;
		}

		return $config;
	}

	/**
	 * Получить список конфигов
	 *
	 * @param array $key_list
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function getList(array $key_list):array {

		// получаем список конфигов
		$config_list = Type_Company_Config::init()->getList($key_list);

		// для каждого ключа
		foreach ($key_list as $key) {

			// если мы получили пустой конфиг - устанавливаем значение для ключа по умолчанию
			if (count($config_list[$key]) < 1) {

				$time   = time();
				$value  = ["value" => Domain_Company_Entity_Config::CONFIG_DEFAULT_VALUE_LIST[$key]];
				$config = new Struct_Db_CompanyData_CompanyConfig($key, $time, $time, $value);
				Gateway_Db_CompanyData_CompanyConfig::insert($config);

				$config_list[$key] = $value;
			}
		}

		return $config_list;
	}

	/**
	 * Установить конфиг
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function set(string $key, mixed $value):void {

		$value = ["value" => $value];

		Type_Company_Config::init()->set($key, [
			"value"      => $value,
			"updated_at" => time(),
		]);
	}

	/**
	 * Получаем значение
	 *
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \queryException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getValue(string $key):array {

		try {

			// получаем из кеша одно значение
			$config = Gateway_Bus_CompanyCache::getConfigKey($key);
		} catch (\cs_RowIsEmpty) {

			// если значения нет в конфиге, пишем его дефолтное значение в конфиг
			$time   = time();
			$value  = ["value" => Domain_Company_Entity_Config::CONFIG_DEFAULT_VALUE_LIST[$key]];
			$config = new Struct_Db_CompanyData_CompanyConfig($key, $time, $time, $value);
			Gateway_Db_CompanyData_CompanyConfig::insert($config);

			// обновляем кеш
			Gateway_Bus_CompanyCache::clearConfigCacheByKey($key);

			return $config->value;
		}

		return $config->value;
	}

	/**
	 * Получаем массив значений конфига
	 *
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \queryException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getListValue(array $key_list):array {

		$config_list = Gateway_Bus_CompanyCache::getConfigKeyList($key_list);

		// для каждого ключа
		foreach ($key_list as $key) {

			// если мы получили пустой конфиг - устанавливаем значение для ключа по умолчанию
			if (!isset($config_list[$key])) {

				// если значения нет в конфиге, пишем его дефолтное значение в конфиг
				$time   = time();
				$value  = ["value" => Domain_Company_Entity_Config::CONFIG_DEFAULT_VALUE_LIST[$key]];
				$config = new Struct_Db_CompanyData_CompanyConfig($key, $time, $time, $value);
				Gateway_Db_CompanyData_CompanyConfig::insert($config);

				// обновляем кеш
				Gateway_Bus_CompanyCache::clearConfigCacheByKey($key);
				$config_list[$key] = $config;
			}
		}

		return $config_list;
	}

	/**
	 * Пишем в значение для конфига
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public static function setValue(string $key, mixed $value):void {

		$set = [
			"value"      => [
				"value" => (int) $value,
			],
			"updated_at" => time(),
		];
		Gateway_Db_CompanyData_CompanyConfig::insertOrUpdate($key, $set);

		// сбрасываем кеш
		Gateway_Bus_CompanyCache::clearConfigCacheByKey($key);
	}
}
