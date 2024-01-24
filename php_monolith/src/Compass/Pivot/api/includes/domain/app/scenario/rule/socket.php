<?php

namespace Compass\Pivot;

/**
 * Сценарии по работе с правилами
 */
class Domain_App_Scenario_Rule_Socket {

	/**
	 * Получить правило
	 *
	 * @param string $name
	 * @param int    $version
	 *
	 * @return array
	 * @throws Domain_App_Exception_Rule_NotFound
	 * @throws Domain_App_Exception_Rule_UnknownHandler
	 */
	public static function get(string $name, int $version):array {

		$rule_config = Domain_App_Entity_Rule_Main::getHandler($version)->getConfig();

		if (!isset($rule_config[$name])) {
			throw new Domain_App_Exception_Rule_NotFound("rule not found");
		}

		return $rule_config[$name];
	}

	/**
	 * Добавить правило
	 *
	 * @param string $name
	 * @param int    $version
	 * @param int    $type
	 * @param int    $priority
	 * @param array  $restrictions
	 * @param array  $values
	 *
	 * @return array
	 * @throws Domain_App_Exception_Rule_AlreadyExists
	 * @throws Domain_App_Exception_Rule_UnknownHandler
	 */
	public static function add(string $name, int $version, int $type, int $priority, array $restrictions, array $values):array {

		$rule_config = Domain_App_Entity_Rule_Main::getHandler($version)->getConfig();

		if (isset($rule_config[$name])) {
			throw new Domain_App_Exception_Rule_AlreadyExists("rule already exists");
		}

		return Domain_App_Entity_Rule_Main::getHandler($version)->add($name, $type, $priority, $restrictions, $values);
	}

	/**
	 * Изменить правило
	 *
	 * @param string $rule_name
	 * @param int    $version
	 * @param array  $restrictions
	 * @param array  $values
	 * @param array  $set
	 *
	 * @return array
	 * @throws Domain_App_Exception_Rule_NotFound
	 * @throws Domain_App_Exception_Rule_UnknownHandler
	 */
	public static function edit(string $rule_name, int $version, array $restrictions, array $values, array $set):array {

		$rule_config = Domain_App_Entity_Rule_Main::getHandler($version)->getConfig();

		if (!isset($rule_config[$rule_name])) {
			throw new Domain_App_Exception_Rule_NotFound("rule not found");
		}

		return Domain_App_Entity_Rule_Main::getHandler($version)->edit($rule_name, $restrictions, $values, $set);
	}

	/**
	 * Удалить правило
	 *
	 * @param string $rule_name
	 * @param int    $version
	 *
	 * @return void
	 * @throws Domain_App_Exception_Feature_UnknownHandler
	 * @throws Domain_App_Exception_Rule_UnknownHandler
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 */
	public static function delete(string $rule_name, int $version):void {

		$rule_config = Domain_App_Entity_Rule_Main::getHandler($version)->getConfig();

		// ничего нет - значит удалено
		if (!isset($rule_config[$rule_name])) {
			return;
		}

		// убираем правило у всех фич на всех платформах
		foreach (\BaseFrame\System\UserAgent::getAvailableAppNameList() as $app_name) {

			foreach (\BaseFrame\System\UserAgent::getAvailablePlatformList() as $platform) {
				self::_updateFeatureConfig($version, $rule_name, $app_name, $platform);
			}
		}

		// удаляем само правило
		Domain_App_Entity_Rule_Main::getHandler($version)->delete($rule_name);
	}

	/**
	 * Обновить конфиг с фичами
	 *
	 * @param int    $version
	 * @param string $rule_name
	 * @param string $app_name
	 * @param string $platform
	 *
	 * @return void
	 * @throws Domain_App_Exception_Feature_UnknownHandler
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 */
	protected static function _updateFeatureConfig(int $version, string $rule_name, string $app_name, string $platform):void {

		$feature_handler = Domain_App_Entity_Feature_Main::getHandler($version);
		$feature_config  = $feature_handler->getConfig($platform, $app_name);

		foreach ($feature_config as $key => $feature) {

			$feature["rule_name_list"] = array_diff($feature["rule_name_list"], [$rule_name]);
			$feature_config[$key]      = $feature;
		}

		Type_System_Config::init()->set($feature_handler->getConfigKey($platform, $app_name), $feature_config);
	}
}