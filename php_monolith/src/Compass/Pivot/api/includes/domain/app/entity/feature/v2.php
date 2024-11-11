<?php

namespace Compass\Pivot;

use BaseFrame\System\File;

/**
 * Работа с фичами второй версии
 */
class Domain_App_Entity_Feature_V2 extends Domain_App_Entity_Feature {

	// версия конфига
	protected const _CONFIG_VERSION = 2;

	// местоположение инициализирующего конфига фич
	protected const _INITIALIZATION_CONFIG_SUBPATH = "feature_v2.json";

	// какой класс с правилами используется для фичи
	protected const _RULE_LIST_CLASS = Domain_App_Entity_Rule_V2::class;

	/**
	 * Получить конфиг в зависимости от платформы
	 *
	 * @param string $platform
	 * @param string $app_name
	 * @param bool   $preserve_keys
	 *
	 * @return array
	 * @throws Domain_App_Exception_Feature_InvalidValue
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 */
	public function getConfig(string $platform, string $app_name, bool $preserve_keys = true):array {

		$output         = [];
		$feature_config = Type_System_Config::init()->getConf($this->getConfigKey($platform, $app_name));

		foreach ($feature_config as $feature_name => $feature_arr) {

			$feature               = Struct_Feature_v2::fromArray(array_merge($feature_arr, ["name" => $feature_name]));
			$output[$feature_name] = $feature->toArrayFull();
		}

		if (!$preserve_keys) {
			return array_values($output);
		}

		return $output;
	}

	/**
	 * Получить конфиг для определенного пользователя
	 *
	 * @param string $platform
	 * @param string $app_name
	 * @param int    $user_id
	 * @param string $app_version
	 *
	 * @return array
	 * @throws Domain_App_Exception_Feature_InvalidValue
	 * @throws Domain_App_Exception_Rule_InvalidValue
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 */
	public function getConfigForUser(string $platform, string $app_name, int $user_id, string $app_version):array {

		$output         = [];
		$feature_config = Type_System_Config::init()->getConf($this->getConfigKey($platform, $app_name));

		$rule_class  = self::_RULE_LIST_CLASS;
		$rule_config = (new $rule_class())->getConfig();

		foreach ($feature_config as $feature_name => $feature_arr) {

			$feature = Struct_Feature_v2::fromArray([
				"name"                   => $feature_name,
				"current_version"        => $feature_arr["current_version"],
				"supported_version_list" => $feature_arr["supported_version_list"],
			]);

			$feature = $this->_addRuleNameList($feature, $feature_arr);

			// если есть привязанные правила - считаем, какую версию надо поставить для пользователя
			if (count($feature->rule_name_list) > 0) {
				$feature = $this->_calculateConfigByRuleList($rule_config, $feature, $user_id, $app_version);
			}

			$output[$feature_name] = $feature->toArrayForUser();
		}

		return $output;
	}

	/**
	 * Инициализировать конфиг
	 *
	 * @param string $platform
	 * @param string $app_name
	 * @param bool   $need_force_update
	 *
	 * @return void
	 * @throws Domain_App_Exception_Feature_InvalidValue
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 * @long
	 */
	public function initializeConfig(string $platform, string $app_name, bool $need_force_update = false):void {

		$config = Type_System_Config::init()->getConf($this->getConfigKey($platform, $app_name));

		if (count($config) > 0 && !$need_force_update) {
			return;
		}

		$initialization_config_file = File::init(self::_INITIALIZATION_CONFIG_DIR, self::_INITIALIZATION_CONFIG_SUBPATH);

		if (!$initialization_config_file->isExists()) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("cant find initialization config");
		}

		$output_config        = [];
		$config_file_contents = $initialization_config_file->read();

		$initialization_config = fromJson($config_file_contents);

		// если json не смогли спарсить - значит фиговый конфиг инициализации - выбрасываем исключение
		if (count($initialization_config) < 1) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("invalid initialization feature config!");
		}

		// для каждой фичи находим версию для платформы и типа приложения
		foreach ($initialization_config as $feature_name => $feature) {

			// если фичи нет для платформы и типа приложения, просто пропускаем
			if (!isset($feature[$platform][$app_name])) {
				continue;
			}

			$feature_config = $feature[$platform][$app_name];

			// если в конфиге нет обязательных полей - значит конфиг неправильный, выкидываем ошибку и прекращаем его формирование
			if (!isset($feature_config["current_version"]) || !isset($feature_config["supported_version_list"])) {
				throw new \BaseFrame\Exception\Domain\ParseFatalException("invalid initialization feature config!");
			}

			// добавляем в конфиг, перед этим пропускаем через объект, исключая левак
			$feature_config["name"]       = $feature_name;
			$output_config[$feature_name] = Struct_Feature_v2::fromArray($feature_config)->toArray();
		}

		// записываем конфиг в базу
		Type_System_Config::init()->set($this->getConfigKey($platform, $app_name), $output_config);
	}

	/**
	 * Добавить новую фичу
	 *
	 * @param string $feature_name
	 * @param string $platform
	 * @param string $app_name
	 * @param array  $set
	 *
	 * @return array
	 * @throws Domain_App_Exception_Feature_AlreadyExists
	 * @throws Domain_App_Exception_Feature_InvalidName
	 * @throws Domain_App_Exception_Feature_InvalidValue
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 */
	public function add(string $feature_name, string $platform, string $app_name, array $set):array {

		$config = Type_System_Config::init()->getConf($this->getConfigKey($platform, $app_name));

		if (isset($config[$feature_name])) {
			throw new Domain_App_Exception_Feature_AlreadyExists("feature already exists");
		}

		$this->_assertValidName($feature_name);

		$feature = Struct_Feature_v2::fromArray([
			"name"                   => trim(mb_strtolower($feature_name)),
			"current_version"        => $set["current_version"],
			"supported_version_list" => $set["supported_version_list"],
		]);

		$config[$feature_name] = $feature->toArray();
		Type_System_Config::init()->set($this->getConfigKey($platform, $app_name), $config);

		return $feature->toArrayFull();
	}

	/**
	 * Изменить фичу
	 *
	 * @param string $feature_name
	 * @param string $platform
	 * @param string $app_name
	 * @param array  $set
	 *
	 * @return array
	 * @throws Domain_App_Exception_Feature_InvalidName
	 * @throws Domain_App_Exception_Feature_InvalidValue
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 */
	public function edit(string $feature_name, string $platform, string $app_name, array $set):array {

		$new_feature_name = "";
		$config           = Type_System_Config::init()->getConf($this->getConfigKey($platform, $app_name));

		// если фичи не существует - выбрасываем ошибку
		if (!isset($config[$feature_name])) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("feature doesnt exist");
		}

		// для каждого значения в переданном массиве
		foreach ($set as $key => $value) {

			// если пытаемся изменить имя фичи - то запоминаем его, чтобы потом узнать, можем ли мы такое сделать
			if ($key === "name" && $feature_name !== $value && $value !== "") {

				$this->_assertValidName($value);
				$new_feature_name = trim(mb_strtolower($value));
				continue;
			}

			// устанавливаем значение
			$config[$feature_name][$key] = $value;
		}

		// меняем имя фичи
		[$feature_name, $config] = $this->_changeName($config, $feature_name, $new_feature_name);

		// форматируем объект, исключая левак и приводя значения к нужным типам
		$feature = Struct_Feature_v2::fromArray([
			"name"                   => $feature_name,
			"current_version"        => $config[$feature_name]["current_version"],
			"supported_version_list" => $config[$feature_name]["supported_version_list"],
			"rule_name_list"         => $config[$feature_name]["rule_name_list"],
		]);

		// формируем в массив для конфига
		$config[$feature_name] = $feature->toArray();

		// записываем конфиг
		Type_System_Config::init()->set($this->getConfigKey($platform, $app_name), $config);

		return $feature->toArrayFull();
	}

	/**
	 * Изменить имя фичи
	 *
	 * @param array  $config
	 * @param string $feature_name
	 * @param string $new_feature_name
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected function _changeName(array $config, string $feature_name, string $new_feature_name):array {

		// если нет нового имени, то просто возвращаем конфиг
		if ($new_feature_name == "") {
			return [$feature_name, $config];
		}

		// если уже существует такая фича - выбрасываем исключение
		if (isset($config[$new_feature_name])) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("feature with this name already exists");
		}

		// перекладываем фичу по новому имени
		$config[$new_feature_name] = $config[$feature_name];
		unset($config[$feature_name]);

		return [$new_feature_name, $config];
	}

	/**
	 * Удалить фичу
	 *
	 * @param string $feature_name
	 * @param string $platform
	 * @param string $app_name
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 */
	public function delete(string $feature_name, string $platform, string $app_name):void {

		$config = Type_System_Config::init()->getConf($this->getConfigKey($platform, $app_name));

		// приводим к utf-8
		$feature_name = urldecode($feature_name);

		// если фичи не существует - значит и удалять нечего
		if (!isset($config[$feature_name])) {
			return;
		}

		// удаляем фичу
		unset($config[$feature_name]);

		// записываем конфиг
		Type_System_Config::init()->set($this->getConfigKey($platform, $app_name), $config);
	}

	/**
	 * Присоединить правило к фиче
	 *
	 * @param string $feature_name
	 * @param string $rule_name
	 * @param string $platform
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
	public function attachRule(string $feature_name, string $rule_name, string $platform, string $app_name):array {

		// получаем конфиги и проверяем, существуют ли фича и правило
		$feature_config = Domain_App_Entity_Feature_Main::getHandler(self::_CONFIG_VERSION)->getConfig($platform, $app_name);
		$rule_config    = Domain_App_Entity_Rule_Main::getHandler(self::_CONFIG_VERSION)->getConfig();

		// если фичи не существует
		if (!isset($feature_config[$feature_name])) {
			throw new Domain_App_Exception_Feature_NotFound("feature not found");
		}

		// если правила не существует
		if (!isset($rule_config[$rule_name])) {
			throw new Domain_App_Exception_Rule_NotFound("rule not found");
		}

		// прикрепляем правило за фичей
		$feature                   = Struct_Feature_v2::fromArray($feature_config[$feature_name]);
		$feature->rule_name_list[] = $rule_name;

		$feature_config[$feature_name] = $feature->toArray();

		// записываем конфиг
		Type_System_Config::init()->set($this->getConfigKey($platform, $app_name), $feature_config);

		return $feature->toArrayFull();
	}

	/**
	 * Открепить правило от фичи
	 *
	 * @param string $feature_name
	 * @param string $rule_name
	 * @param string $platform
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
	public function detachRule(string $feature_name, string $rule_name, string $platform, string $app_name):array {

		// получаем конфиги и проверяем, существуют ли фича и правило
		$feature_config = Domain_App_Entity_Feature_Main::getHandler(self::_CONFIG_VERSION)->getConfig($platform, $app_name);
		$rule_config    = Domain_App_Entity_Rule_Main::getHandler(self::_CONFIG_VERSION)->getConfig();

		// если фичи не существует
		if (!isset($feature_config[$feature_name])) {
			throw new Domain_App_Exception_Feature_NotFound("feature not found");
		}

		// если правила не существует
		if (!isset($rule_config[$rule_name])) {
			throw new Domain_App_Exception_Rule_NotFound("rule not found");
		}

		// прикрепляем правило за фичей
		$feature                 = Struct_Feature_v2::fromArray($feature_config[$feature_name]);
		$feature->rule_name_list = array_diff($feature->rule_name_list, [$rule_name]);

		$feature_config[$feature_name] = $feature->toArray();

		// записываем конфиг
		Type_System_Config::init()->set($this->getConfigKey($platform, $app_name), $feature_config);

		return $feature->toArrayFull();
	}

	/**
	 * Проверяем, что валидное имя
	 *
	 * @param string $feature_name
	 *
	 * @return void
	 * @throws Domain_App_Exception_Feature_InvalidName
	 */
	protected function _assertValidName(string $feature_name):void {

		if (!preg_match("/^[a-zA-Z0-9-_]+$/", $feature_name)) {
			throw new Domain_App_Exception_Feature_InvalidName("passed invalid name");
		}
	}

	/**
	 * Добавить названия кастомных правил
	 *
	 * @param Struct_Feature_v2 $feature
	 * @param array             $set
	 *
	 * @return Struct_Feature_v2
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected function _addRuleNameList(Struct_Feature_v2 $feature, array $set):Struct_Feature_v2 {

		if (isset($set["rule_name_list"])) {

			$rule_config             = Domain_App_Entity_Rule_Main::getHandler(self::_CONFIG_VERSION)->getConfig();
			$existing_rule_name_list = array_intersect($set["rule_name_list"], array_keys($rule_config));
			$feature                 = $feature->addRuleNameList($existing_rule_name_list);
		}

		return $feature;
	}

	/**
	 * Посчитать итогове значение версии для фичи с помощью списка правил
	 *
	 * @param array             $rule_config
	 * @param Struct_Feature_v2 $feature
	 * @param int               $user_id
	 * @param string            $app_version
	 *
	 * @return Struct_Feature_v2
	 * @throws Domain_App_Exception_Rule_InvalidValue
	 */
	protected function _calculateConfigByRuleList(array             $rule_config,
								    Struct_Feature_v2 $feature, int $user_id, string $app_version):Struct_Feature_v2 {

		$max_priority = null;
		$min_type     = null;

		// проходимся по правилам
		foreach ($feature->rule_name_list as $rule_name) {

			// получаем правило по id, если его нет, пропускаем, чтобы ничего не ломать
			if (!isset($rule_config[$rule_name])) {
				continue;
			}
			$rule = Struct_Rule_V2::fromArray($rule_config[$rule_name]);

			// если правило надо применить - применяем
			if (Domain_App_Entity_Rule_V2::isNeedApplyRule($rule, $max_priority, $min_type, $user_id, $app_version)) {

				$feature->current_version        = $rule->values->current_version;
				$feature->supported_version_list = $rule->values->supported_version_list;
				$max_priority                    = $rule->priority;
				$min_type                        = $rule->type;
			}
		}

		// отдаем фичу
		return $feature;
	}
}