<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;
use BaseFrame\System\File;

/**
 * класс для инициализации конфига
 */
class Type_App_Config {

	/**
	 * Метод инициализации конфига
	 *
	 * @long Потому что это конфиг
	 *
	 * !!! warning - если добавляется новая платформа, то необходимо так же добавить в update
	 */
	public static function start():void {

		$feature_data = fromJson(File::init(PIVOT_MODULE_ROOT . "conf", "feature_v1.json")->read());
		$rule_data    = fromJson(File::init(PIVOT_MODULE_ROOT . "conf", "rule_v1.json")->read());

		$feature_electron_compass = $feature_data[Type_Api_Platform::PLATFORM_ELECTRON . Domain_User_Entity_Feature::COMPASS_POSTFIX];
		$feature_electron_comteam = $feature_data[Type_Api_Platform::PLATFORM_ELECTRON . Domain_User_Entity_Feature::COMTEAM_POSTFIX];

		self::_setIfEmpty(Domain_User_Entity_Feature::RULES_KEY, $rule_data);
		self::_setIfEmpty(
			Domain_User_Entity_Feature::PLATFORM_CONFIG_ALIAS[Type_Api_Platform::PLATFORM_ELECTRON . Domain_User_Entity_Feature::COMPASS_POSTFIX],
			$feature_electron_compass
		);
		self::_setIfEmpty(
			Domain_User_Entity_Feature::PLATFORM_CONFIG_ALIAS[Type_Api_Platform::PLATFORM_ELECTRON . Domain_User_Entity_Feature::COMTEAM_POSTFIX],
			$feature_electron_comteam
		);
	}

	/**
	 * Метод обновления конфига (только для сервера онпремайза)
	 *
	 * @long это конфиг
	 *
	 * !!! warning - если добавляется новая платформа, то необходимо так же добавить в start
	 */
	public static function update():void {

		if (!ServerProvider::isOnPremise()) {
			return;
		}

		$feature_data = fromJson(File::init(PIVOT_MODULE_ROOT . "conf", "feature_v1.json")->read());
		$rule_data    = fromJson(File::init(PIVOT_MODULE_ROOT . "conf", "rule_v1.json")->read());

		$feature_electron_compass = $feature_data[Type_Api_Platform::PLATFORM_ELECTRON . Domain_User_Entity_Feature::COMPASS_POSTFIX];
		$feature_electron_comteam = $feature_data[Type_Api_Platform::PLATFORM_ELECTRON . Domain_User_Entity_Feature::COMTEAM_POSTFIX];

		$feature_iphone_compass = $feature_data[Type_Api_Platform::PLATFORM_IOS . Domain_User_Entity_Feature::COMPASS_POSTFIX];
		$feature_iphone_comteam = $feature_data[Type_Api_Platform::PLATFORM_IOS . Domain_User_Entity_Feature::COMTEAM_POSTFIX];

		$feature_android_compass = $feature_data[Type_Api_Platform::PLATFORM_ANDROID . Domain_User_Entity_Feature::COMPASS_POSTFIX];
		$feature_android_comteam = $feature_data[Type_Api_Platform::PLATFORM_ANDROID . Domain_User_Entity_Feature::COMTEAM_POSTFIX];

		self::_updateConfig(Domain_User_Entity_Feature::RULES_KEY, $rule_data);
		self::_updateConfig(
			Domain_User_Entity_Feature::PLATFORM_CONFIG_ALIAS[Type_Api_Platform::PLATFORM_ELECTRON . Domain_User_Entity_Feature::COMPASS_POSTFIX],
			$feature_electron_compass
		);
		self::_updateConfig(
			Domain_User_Entity_Feature::PLATFORM_CONFIG_ALIAS[Type_Api_Platform::PLATFORM_ELECTRON . Domain_User_Entity_Feature::COMTEAM_POSTFIX],
			$feature_electron_comteam
		);
		self::_updateConfig(
			Domain_User_Entity_Feature::PLATFORM_CONFIG_ALIAS[Type_Api_Platform::PLATFORM_IOS . Domain_User_Entity_Feature::COMPASS_POSTFIX],
			$feature_iphone_compass
		);
		self::_updateConfig(
			Domain_User_Entity_Feature::PLATFORM_CONFIG_ALIAS[Type_Api_Platform::PLATFORM_IOS . Domain_User_Entity_Feature::COMTEAM_POSTFIX],
			$feature_iphone_comteam
		);
		self::_updateConfig(
			Domain_User_Entity_Feature::PLATFORM_CONFIG_ALIAS[Type_Api_Platform::PLATFORM_ANDROID . Domain_User_Entity_Feature::COMPASS_POSTFIX],
			$feature_android_compass
		);
		self::_updateConfig(
			Domain_User_Entity_Feature::PLATFORM_CONFIG_ALIAS[Type_Api_Platform::PLATFORM_ANDROID . Domain_User_Entity_Feature::COMTEAM_POSTFIX],
			$feature_android_comteam
		);
	}

	/**
	 * Установить значение конфига, если ранее не был установлен
	 *
	 */
	protected static function _setIfEmpty(string $key, array $value):void {

		if (count(Type_System_Config::init()->getConf($key)) === 0) {
			self::_updateConfig($key, $value);
		}
	}

	/**
	 * Обновить значение конфига
	 *
	 */
	protected static function _updateConfig(string $key, array $value):void {

		Type_System_Config::init()->set($key, $value);
	}
}
