<?php

namespace Compass\Pivot;

/**
 * Родительский класс для конфигов с фичами
 */
abstract class Domain_App_Entity_Feature {

	protected const _CONFIG_VERSION = 0;

	// в какой папке находятся конфиги инициализации
	protected const _INITIALIZATION_CONFIG_DIR = PIVOT_MODULE_ROOT . "/conf";

	/**
	 * Получить версию списка функционала
	 *
	 * @return int
	 */
	public function getConfigVersion():int {

		return static::_CONFIG_VERSION;
	}

	/**
	 * Инициализировать конфиг
	 *
	 * @param string $platform
	 * @param string $app_name
	 * @param bool   $need_force_update
	 *
	 * @return void
	 */
	abstract public function initializeConfig(string $platform, string $app_name, bool $need_force_update = false):void;

	/**
	 * Получить список функционала
	 *
	 * @param string $platform
	 * @param string $app_name
	 * @param bool   $preserve_keys
	 *
	 * @return array
	 */
	abstract public function getConfig(string $platform, string $app_name, bool $preserve_keys = true):array;

	/**
	 * Получить список функционала для пользователя
	 *
	 * @param string $platform
	 * @param string $app_name
	 * @param int    $user_id
	 * @param string $app_version
	 *
	 * @return array
	 */
	abstract public function getConfigForUser(string $platform, string $app_name, int $user_id, string $app_version):array;

	/**
	 * Получить ключ конфига для фичи
	 *
	 * @param string $platform
	 * @param string $app_name
	 *
	 * @return mixed
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 */
	public static function getConfigKey(string $platform, string $app_name):string {

		\BaseFrame\System\UserAgent::assertPlatformAvailable($platform);
		\BaseFrame\System\UserAgent::assertAppNameAvailable($app_name);

		return mb_strtoupper("FEATURE_V" . static::_CONFIG_VERSION . "_{$platform}_{$app_name}");
	}

	/**
	 * Добавить фичу
	 *
	 * @param string $feature_name
	 * @param string $platform
	 * @param string $app_name
	 * @param array  $set
	 *
	 * @return array
	 */
	abstract public function add(string $feature_name, string $platform, string $app_name, array $set):array;

	/**
	 * Изменить фичу
	 *
	 * @param string $feature_name
	 * @param string $platform
	 * @param string $app_name
	 * @param array  $set
	 *
	 * @return array
	 */
	abstract public function edit(string $feature_name, string $platform, string $app_name, array $set):array;

	/**
	 * Удалить фичу
	 *
	 * @param string $feature_name
	 * @param string $platform
	 * @param string $app_name
	 *
	 * @return void
	 */
	abstract public function delete(string $feature_name, string $platform, string $app_name):void;
}