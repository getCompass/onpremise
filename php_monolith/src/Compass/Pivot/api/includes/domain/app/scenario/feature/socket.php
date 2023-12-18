<?php

namespace Compass\Pivot;

/**
 * Сценарии по работе с фичами
 */
class Domain_App_Scenario_Feature_Socket {

	/**
	 * Получить фичу
	 *
	 * @param string $feature_name
	 * @param int    $version
	 * @param string $platform
	 * @param string $app_name
	 *
	 * @return array
	 * @throws Domain_App_Exception_Feature_InvalidValue
	 * @throws Domain_App_Exception_Feature_NotFound
	 * @throws Domain_App_Exception_Feature_UnknownHandler
	 */
	public static function get(string $feature_name, int $version, string $platform, string $app_name):array {

		// приводим к utf-8
		$feature_name = urldecode($feature_name);

		$config = Domain_App_Entity_Feature_Main::getHandler($version)->getConfig($platform, $app_name);

		if (!isset($config[$feature_name])) {
			throw new Domain_App_Exception_Feature_NotFound("feature not found");
		}

		return Struct_Feature_v2::fromArray(array_merge($config[$feature_name], ["name" => $feature_name]))->toArrayFull();
	}

	/**
	 * Добавить или изменить фичу
	 *
	 * @param string $feature_name
	 * @param int    $version
	 * @param string $platform
	 * @param string $app_name
	 * @param array  $set
	 *
	 * @return array
	 * @throws Domain_App_Exception_Feature_UnknownHandler
	 */
	public static function addOrEdit(string $feature_name, int $version, string $platform, string $app_name, array $set):array {

		$feature_handler = Domain_App_Entity_Feature_Main::getHandler($version);

		try {
			$feature = $feature_handler->add($feature_name, $platform, $app_name, $set);
		} catch (Domain_App_Exception_Feature_AlreadyExists) {
			$feature = $feature_handler->edit($feature_name, $platform, $app_name, $set);
		}

		return $feature;
	}

	/**
	 * Прикрепить правило
	 *
	 * @param string $feature_name
	 * @param string $rule_name
	 * @param int    $version
	 * @param string $platform
	 *
	 * @param string $app_name
	 *
	 * @return array
	 * @throws Domain_App_Exception_Feature_InvalidValue
	 * @throws Domain_App_Exception_Feature_NotFound
	 * @throws Domain_App_Exception_Feature_UnknownHandler
	 * @throws Domain_App_Exception_Rule_NotFound
	 * @throws Domain_App_Exception_Rule_UnknownHandler
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 */
	public static function attachRule(string $feature_name, string $rule_name, int $version, string $platform, string $app_name):array {

		return Domain_App_Entity_Feature_Main::getHandler($version)->attachRule($feature_name, $rule_name, $platform, $app_name);
	}

	/**
	 * Открепить правило
	 *
	 * @param string $feature_name
	 * @param string $rule_name
	 * @param int    $version
	 * @param string $platform
	 *
	 * @param string $app_name
	 *
	 * @return array
	 * @throws Domain_App_Exception_Feature_InvalidValue
	 * @throws Domain_App_Exception_Feature_NotFound
	 * @throws Domain_App_Exception_Feature_UnknownHandler
	 * @throws Domain_App_Exception_Rule_NotFound
	 * @throws Domain_App_Exception_Rule_UnknownHandler
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 */
	public static function detachRule(string $feature_name, string $rule_name, int $version, string $platform, string $app_name):array {

		return Domain_App_Entity_Feature_Main::getHandler($version)->detachRule($feature_name, $rule_name, $platform, $app_name);
	}
}