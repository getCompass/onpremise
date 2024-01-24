<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер для работы с конфигом фич
 */
class Socket_Feature extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"getRawConfig",
		"get",
		"addOrEdit",
		"delete",
		"changeName",
		"addRule",
		"deleteRule",
		"editRule",
		"getRuleList",
		"getRule",
		"attachRuleToFeature",
		"detachRuleFromFeature",
		"getFullConfig",
	];

	/**
	 * получить весь конфиг по платформе
	 */
	public function getRawConfig():array {

		$platform       = $this->post(\Formatter::TYPE_STRING, "platform");
		$app_name       = $this->post(\Formatter::TYPE_STRING, "app_name", "");
		$config_version = $this->post(\Formatter::TYPE_INT, "config_version", 1);

		try {

			// получаем конфиг
			if ($config_version <= 1) {

				$config_key = $this->_getConfigKeyByPlatform($platform);
				$config     = Type_System_Config::init()->getConf($config_key);
			} else {
				$config = Domain_App_Entity_Feature_Main::getHandler($config_version)->getConfig($platform, $app_name);
			}
		} catch (Domain_App_Exception_Feature_UnknownHandler) {
			return $this->error(1413006, "unknown handler");
		}

		return $this->ok([
			"config" => (array) $config,
		]);
	}

	/**
	 * получить весь конфиг по всем платформам
	 */
	public function getFullConfig():array {

		$config = [];

		$config_version = $this->post(\Formatter::TYPE_INT, "config_version", 2);
		$feature_handler = Domain_App_Entity_Feature_Main::getHandler($config_version);

		try {
			foreach (\BaseFrame\System\UserAgent::getAvailableAppNameList() as $app_name) {

				foreach (\BaseFrame\System\UserAgent::getAvailablePlatformList() as $platform) {
					$config[$app_name][$platform] = $feature_handler->getConfig($platform, $app_name, false);
				}
			}
		} catch (Domain_App_Exception_Feature_UnknownHandler) {
			return $this->error(1413006, "unknown handler");
		}

		return $this->ok([
			"config" => (array) $config,
		]);
	}

	/**
	 * получить весь конфиг по платформе
	 */
	public function get():array {

		$platform       = $this->post(\Formatter::TYPE_STRING, "platform");
		$app_name       = $this->post(\Formatter::TYPE_STRING, "app_name", "");
		$feature_name   = $this->post(\Formatter::TYPE_STRING, "name");
		$config_version = $this->post(\Formatter::TYPE_INT, "config_version", 1);

		try {
			$feature = Domain_App_Scenario_Feature_Socket::get($feature_name, $config_version, $platform, $app_name);
		} catch (Domain_App_Exception_Feature_NotFound) {
			return $this->error(1413001, "feature not found");
		} catch (Domain_App_Exception_Feature_UnknownHandler) {
			return $this->error(1413006, "unknown handler");
		}

		return $this->ok([
			"feature" => (object) $feature,
		]);
	}

	/**
	 * добавляет/редактируем фичу
	 */
	public function addOrEdit():array {

		return match ($this->method_version) {
			1 => $this->_addOrEditV1(),
			2 => $this->_addOrEditV2(),
		};
	}

	/**
	 * Добавить или обновить фичу - версия 1
	 *
	 * @return array
	 * @throws ParamException
	 */
	protected function _addOrEditV1():array {

		$platform     = $this->post(\Formatter::TYPE_STRING, "platform");
		$feature_name = $this->post(\Formatter::TYPE_STRING, "name");
		$value        = $this->post(\Formatter::TYPE_FLOAT, "value");

		if (mb_strlen($feature_name) < 1) {
			throw new ParamException("received unknown feature_name");
		}

		// получаем конфиг
		$config_key = $this->_getConfigKeyByPlatform($platform);
		$config     = Type_System_Config::init()->getConf($config_key);

		// обновляем конфиг
		$config = Domain_User_Entity_Feature::addOrEdit($config, $feature_name, $value);
		Type_System_Config::init()->set($config_key, $config);

		return $this->ok();
	}

	/**
	 * Добавить или обновить фичу - версия 2
	 *
	 * @return array
	 * @throws ParamException
	 */
	protected function _addOrEditV2():array {

		$platform       = $this->post(\Formatter::TYPE_STRING, "platform");
		$app_name       = $this->post(\Formatter::TYPE_STRING, "app_name", "");
		$feature_name   = $this->post(\Formatter::TYPE_STRING, "name");
		$set            = $this->post(\Formatter::TYPE_ARRAY, "set");
		$config_version = $this->post(\Formatter::TYPE_INT, "config_version", 1);

		try {
			$feature = Domain_App_Scenario_Feature_Socket::addOrEdit($feature_name, $config_version, $platform, $app_name, $set);
		} catch (Domain_App_Exception_Feature_InvalidValue) {
			return $this->error(1413005, "invalid values for rule");
		} catch (Domain_App_Exception_Feature_InvalidName) {
			return $this->error(1413007, "invalid name for feature");
		} catch (Domain_App_Exception_Feature_UnknownHandler) {
			return $this->error(1413006, "unknown handler");
		}

		return $this->ok([
			"feature" => (object) $feature,
		]);
	}

	/**
	 * Удаляем фичу
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function delete():array {

		$platform       = $this->post(\Formatter::TYPE_STRING, "platform");
		$app_name       = $this->post(\Formatter::TYPE_STRING, "app_name", "");
		$feature_name   = $this->post(\Formatter::TYPE_STRING, "name");
		$config_version = $this->post(\Formatter::TYPE_INT, "config_version", 1);

		if (mb_strlen($feature_name) < 1) {
			throw new ParamException("received unknown feature_name");
		}

		// удаляем фичу
		if ($config_version <= 1) {

			// получаем конфиг
			$config_key = $this->_getConfigKeyByPlatform($platform);
			$config     = Type_System_Config::init()->getConf($config_key);

			// обновляем конфиг
			$config = Domain_User_Entity_Feature::delete($config, $feature_name);
			Type_System_Config::init()->set($config_key, $config);
			return $this->ok();
		}

		try {
			Domain_App_Entity_Feature_Main::getHandler()->delete($feature_name, $platform, $app_name);
		} catch (Domain_App_Exception_Feature_UnknownHandler) {
			return $this->error(1413006, "unknown handler");
		}

		return $this->ok();
	}

	/**
	 * изменяем имя фичи
	 */
	public function changeName():array {

		$platform         = $this->post(\Formatter::TYPE_STRING, "platform");
		$feature_name     = $this->post(\Formatter::TYPE_STRING, "name");
		$new_feature_name = $this->post(\Formatter::TYPE_STRING, "new_name");

		if (mb_strlen($feature_name) < 1) {
			throw new ParamException("received empty feature_name");
		}

		if (mb_strlen($new_feature_name) < 1) {
			throw new ParamException("received empty new_feature_name");
		}

		// получаем конфиг
		$config_key = $this->_getConfigKeyByPlatform($platform);
		$config     = Type_System_Config::init()->getConf($config_key);

		// изменяем имя
		try {
			$config = Domain_User_Entity_Feature::changeFeatureName($config, $feature_name, $new_feature_name);
		} catch (cs_FeatureNotFound) {
			throw new ParamException("feature not found");
		}

		// обновляем конфиг
		Type_System_Config::init()->set($config_key, $config);

		return $this->ok();
	}

	/**
	 * Добавить правило
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function addRule():array {

		return match ($this->method_version) {
			1 => $this->_addRuleV1(),
			2 => $this->_addRuleV2(),
		};
	}

	/**
	 * добавить новое правило версия 1
	 */
	protected function _addRuleV1():array {

		// имя правила, если создаем именное правило
		$rule_name = $this->post("?s", "rule_name", "");

		// данные о правиле
		$rule_type    = $this->post("?i", "rule_type", 0);
		$value        = $this->post("?f", "value", 0);
		$user_id_list = $this->post("?a", "user_id_list", []);
		$version      = $this->post("?s", "version", "");

		// проверяем тип
		if (!Domain_User_Entity_Feature::isAllowRuleType($rule_type)) {
			throw new ParamException("received unknown rule_type = $rule_type");
		}

		// форматируем id из string в int
		$user_id_list = $this->_doFormatUserIdList($user_id_list);

		// получаем правила
		$rules = Type_System_Config::init()->getConf(Domain_User_Entity_Feature::RULES_KEY);

		// добавляем правило
		try {
			[$rule_id, $rules] = Domain_User_Entity_Feature::addRule($rules, $rule_type, $value, $user_id_list, $version, $rule_name);
		} catch (cs_RuleAlreadyExists) {
			throw new ParamException("named rule already exists");
		}

		// сохраняем правила
		Type_System_Config::init()->set(Domain_User_Entity_Feature::RULES_KEY, $rules);

		return $this->ok([
			"rule_id" => (int) $rule_id,
		]);
	}

	/**
	 * Добавить правило версия 2
	 *
	 * @return array
	 * @throws ParamException
	 */
	protected function _addRuleV2():array {

		$name           = $this->post(\Formatter::TYPE_STRING, "name");
		$config_version = $this->post(\Formatter::TYPE_INT, "config_version");
		$type           = $this->post(\Formatter::TYPE_INT, "type");
		$priority       = $this->post(\Formatter::TYPE_INT, "priority");
		$restrictions   = $this->post(\Formatter::TYPE_ARRAY, "restrictions");
		$values         = $this->post(\Formatter::TYPE_ARRAY, "values");

		// редактируем правило
		try {
			$rule = Domain_App_Scenario_Rule_Socket::add($name, $config_version, $type, $priority, $restrictions, $values);
		} catch (Domain_App_Exception_Rule_AlreadyExists) {
			return $this->error(1413002, "rule already exists");
		} catch (Domain_App_Exception_Rule_InvalidValue) {
			return $this->error(1413004, "invalid values for rule");
		} catch (Domain_App_Exception_Rule_UnknownHandler) {
			return $this->error(1413006, "unknown handler");
		} catch (Domain_App_Exception_Rule_InvalidName) {
			return $this->error(1413007, "invalid name for rule");
		}

		return $this->ok([
			"rule" => (object) $rule,
		]);
	}

	/**
	 * Удалить правило
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 */
	public function deleteRule():array {

		return match ($this->method_version) {
			1 => $this->_deleteRuleV1(),
			2 => $this->_deleteRuleV2(),
		};
	}

	/**
	 * удалить правило - первая версия
	 */
	public function _deleteRuleV1():array {

		// номер правила
		$rule_id = $this->post("?i", "rule_id");

		// получаем конфиги
		$all_configs = [];
		foreach (Domain_User_Entity_Feature::PLATFORM_CONFIG_ALIAS as $name => $key) {
			$all_configs[$name] = Type_System_Config::init()->getConf($key);
		}

		// получаем правила
		$rules = Type_System_Config::init()->getConf(Domain_User_Entity_Feature::RULES_KEY);

		// удаляем правило
		try {
			[$all_configs, $rules] = Domain_User_Entity_Feature::deleteRule($all_configs, $rules, $rule_id);
		} catch (cs_RuleNotFound) {
			throw new ParamException("rule not found");
		}

		// сохраняем конфиги
		foreach (Domain_User_Entity_Feature::PLATFORM_CONFIG_ALIAS as $name => $key) {
			Type_System_Config::init()->set($key, $all_configs[$name]);
		}
		// сохраняем правила
		Type_System_Config::init()->set(Domain_User_Entity_Feature::RULES_KEY, $rules);

		return $this->ok();
	}

	/**
	 * Удалить правило - вторая версия
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 */
	protected function _deleteRuleV2():array {

		$name           = $this->post(\Formatter::TYPE_STRING, "name");
		$config_version = $this->post(\Formatter::TYPE_INT, "config_version");

		try {
			Domain_App_Scenario_Rule_Socket::delete($name, $config_version);
		} catch (Domain_App_Exception_Rule_UnknownHandler|Domain_App_Exception_Feature_UnknownHandler) {
			return $this->error(1413006, "unknown handler");
		}

		return $this->ok();
	}

	/**
	 * отредактировать правило
	 */
	public function editRule():array {

		return match ($this->method_version) {
			1 => $this->_editRuleV1(),
			2 => $this->_editRuleV2(),
		};
	}

	/**
	 * Изменить правило версия 1
	 *
	 * @return array
	 * @throws ParamException
	 */
	protected function _editRuleV1():array {

		$rule_id      = $this->post("?i", "rule_id");
		$rule_type    = $this->post("?i", "rule_type");
		$value        = $this->post("?f", "value");
		$user_id_list = $this->post("?a", "user_id_list", []);
		$version      = $this->post("?s", "version", "");
		$rule_name    = $this->post("?s", "rule_name", "");

		if (!Domain_User_Entity_Feature::isAllowRuleType($rule_type)) {
			throw new ParamException("received unknown rule_type = $rule_type");
		}

		$user_id_list = $this->_doFormatUserIdList($user_id_list);

		$rules = Type_System_Config::init()->getConf(Domain_User_Entity_Feature::RULES_KEY);

		// редактируем правило
		try {
			$rules = Domain_User_Entity_Feature::editRule($rules, $rule_id, $rule_type, $value, $user_id_list, $version, $rule_name);
		} catch (cs_RuleConvertingToUnnamed) {
			throw new ParamException("rule cannot be converted from named to unnamed");
		} catch (cs_RuleNotFound) {
			throw new ParamException("rule not found");
		} catch (cs_RuleAlreadyExists) {
			throw new ParamException("rule already exists");
		}

		Type_System_Config::init()->set(Domain_User_Entity_Feature::RULES_KEY, $rules);

		return $this->ok();
	}

	/**
	 * Изменить правило, версия 2
	 *
	 * @return array
	 * @throws ParamException
	 */
	protected function _editRuleV2():array {

		$name           = $this->post(\Formatter::TYPE_STRING, "name");
		$config_version = $this->post(\Formatter::TYPE_STRING, "config_version");
		$restrictions   = $this->post(\Formatter::TYPE_ARRAY, "restrictions", []);
		$values         = $this->post(\Formatter::TYPE_ARRAY, "values", []);
		$set            = $this->post(\Formatter::TYPE_ARRAY, "set", []);

		// редактируем правило
		try {
			$rule = Domain_App_Scenario_Rule_Socket::edit($name, $config_version, $restrictions, $values, $set);
		} catch (Domain_App_Exception_Rule_NotFound) {
			return $this->error(1413003, "rule not found");
		} catch (Domain_App_Exception_Rule_InvalidValue) {
			return $this->error(1413004, "invalid values for rule");
		} catch (Domain_App_Exception_Rule_UnknownHandler) {
			return $this->error(1413006, "unknown handler");
		} catch (Domain_App_Exception_Rule_InvalidName) {
			return $this->error(1413007, "invalid name for rule");
		}

		return $this->ok([
			"rule" => (object) $rule,
		]);
	}

	/**
	 * Получить список общих правил
	 * @return array
	 * @throws ParamException
	 */
	public function getRuleList():array {

		$config_version = $this->post(\Formatter::TYPE_INT, "config_version", 1);

		try {

			// получаем общие правила
			$rule_list = match ($config_version) {
				1       => Type_System_Config::init()->getConf(Domain_User_Entity_Feature::RULES_KEY),
				default => Domain_App_Entity_Rule_Main::getHandler($config_version)->getConfig(false)
			};
		} catch (Domain_App_Exception_Rule_UnknownHandler) {
			return $this->error(1413006, "unknown handler");
		}

		return $this->ok([
			"rule_list" => (array) $rule_list,
		]);
	}

	/**
	 * Получить правило
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function getRule():array {

		$config_version = $this->post(\Formatter::TYPE_INT, "config_version", 1);
		$name           = $this->post(\Formatter::TYPE_STRING, "name");

		try {
			$rule = Domain_App_Scenario_Rule_Socket::get($name, $config_version);
		} catch (Domain_App_Exception_Rule_NotFound) {
			return $this->error(1413003, "rule not found");
		} catch (Domain_App_Exception_Rule_UnknownHandler) {
			return $this->error(1413006, "unknown handler");
		}

		return $this->ok([
			"rule" => (object) $rule,
		]);
	}

	/**
	 * Добавить правило к фиче
	 *
	 * @return array
	 * @throws Domain_App_Exception_Feature_InvalidValue
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 */
	public function attachRuleToFeature():array {

		return match ($this->method_version) {
			1 => $this->_attachRuleToFeatureV1(),
			2 => $this->_attachRuleToFeatureV2(),
		};
	}

	/**
	 * прикрепить правило к фиче версия 1
	 */
	protected function _attachRuleToFeatureV1():array {

		$platform     = $this->post("?s", "platform");
		$feature_name = $this->post("?s", "name");
		$rule_id      = $this->post("?i", "rule_id");
		$priority     = $this->post("?i", "priority", 0);

		if (mb_strlen($feature_name) < 1) {
			throw new ParamException("received unknown feature_name");
		}

		// получаем конфиг
		$config_key = $this->_getConfigKeyByPlatform($platform);
		$config     = Type_System_Config::init()->getConf($config_key);

		// получаем правила
		$rules = Type_System_Config::init()->getConf(Domain_User_Entity_Feature::RULES_KEY);

		// крепим правило к фиче
		try {
			$config = Domain_User_Entity_Feature::attachRuleToFeature($config, $feature_name, $rules, $rule_id, $priority);
		} catch (cs_RuleNotFound) {
			throw new ParamException("rule not found");
		} catch (cs_FeatureNotFound) {
			throw new ParamException("feature not found");
		}

		// обновляем конфиг
		Type_System_Config::init()->set($config_key, $config);

		return $this->ok();
	}

	/**
	 * Прикрепить правило к фичу версия 2
	 *
	 * @return array
	 * @throws Domain_App_Exception_Feature_InvalidValue
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 */
	protected function _attachRuleToFeatureV2():array {

		$platform       = $this->post(\Formatter::TYPE_STRING, "platform");
		$app_name       = $this->post(\Formatter::TYPE_STRING, "app_name");
		$config_version = $this->post(\Formatter::TYPE_INT, "config_version");
		$feature_name   = $this->post(\Formatter::TYPE_STRING, "feature_name");
		$rule_name      = $this->post(\Formatter::TYPE_STRING, "rule_name");

		try {
			$feature = Domain_App_Scenario_Feature_Socket::attachRule($feature_name, $rule_name, $config_version, $platform, $app_name);
		} catch (Domain_App_Exception_Feature_NotFound) {
			return $this->error(1413001, "feature not found");
		} catch (Domain_App_Exception_Rule_NotFound) {
			return $this->error(1413003, "rule not found");
		} catch (Domain_App_Exception_Feature_UnknownHandler|Domain_App_Exception_Rule_UnknownHandler) {
			return $this->error(1413006, "unknown handler");
		}

		return $this->ok([
			"feature" => (object) $feature,
		]);
	}

	/**
	 * Открепить правило от фичи
	 *
	 * @return array
	 * @throws Domain_App_Exception_Feature_InvalidValue
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 */
	public function detachRuleFromFeature():array {

		return match ($this->method_version) {
			1 => $this->_detachRuleFromFeatureV1(),
			2 => $this->_detachRuleFromFeatureV2()
		};
	}

	/**
	 * открепить правило от фичи
	 */
	protected function _detachRuleFromFeatureV1():array {

		$platform     = $this->post("?s", "platform");
		$feature_name = $this->post("?s", "name");
		$rule_id      = $this->post("?i", "rule_id");

		if (mb_strlen($feature_name) < 1) {
			throw new ParamException("received unknown feature_name");
		}

		// получаем конфиг
		$config_key = $this->_getConfigKeyByPlatform($platform);
		$config     = Type_System_Config::init()->getConf($config_key);

		// получаем правила
		$rules = Type_System_Config::init()->getConf(Domain_User_Entity_Feature::RULES_KEY);

		// открепляем правило от фичи
		try {
			$config = Domain_User_Entity_Feature::detachRuleFromFeature($config, $feature_name, $rules, $rule_id);
		} catch (cs_RuleNotFound) {
			throw new ParamException("rule not found");
		} catch (cs_FeatureNotFound) {
			throw new ParamException("feature not found");
		}

		// обновляем конфиг
		Type_System_Config::init()->set($config_key, $config);

		return $this->ok();
	}

	/**
	 * Открепить правило от фичи версия 2
	 *
	 * @return array
	 * @throws Domain_App_Exception_Feature_InvalidValue
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Request\AppNameNotFoundException
	 * @throws \BaseFrame\Exception\Request\PlatformNotFoundException
	 */
	protected function _detachRuleFromFeatureV2():array {

		$platform       = $this->post(\Formatter::TYPE_STRING, "platform");
		$app_name       = $this->post(\Formatter::TYPE_STRING, "app_name");
		$config_version = $this->post(\Formatter::TYPE_INT, "config_version");
		$feature_name   = $this->post(\Formatter::TYPE_STRING, "feature_name");
		$rule_name      = $this->post(\Formatter::TYPE_STRING, "rule_name");

		try {
			$feature = Domain_App_Scenario_Feature_Socket::detachRule($feature_name, $rule_name, $config_version, $platform, $app_name);
		} catch (Domain_App_Exception_Feature_NotFound) {
			return $this->error(1413001, "feature not found");
		} catch (Domain_App_Exception_Rule_NotFound) {
			return $this->error(1413003, "rule not found");
		} catch (Domain_App_Exception_Feature_UnknownHandler|Domain_App_Exception_Rule_UnknownHandler) {
			return $this->error(1413006, "unknown handler");
		}

		return $this->ok([
			"feature" => (object) $feature,
		]);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получаем ключ конфиг по платформе
	 *
	 * @throws \paramException
	 */
	protected function _getConfigKeyByPlatform(string $platform):string {

		// проверяем валидность платформы
		if (!isset(Domain_User_Entity_Feature::PLATFORM_CONFIG_ALIAS[$platform])) {
			throw new ParamException("unknown platform");
		}
		return Domain_User_Entity_Feature::PLATFORM_CONFIG_ALIAS[$platform];
	}

	/**
	 * Форматируем user_id_list
	 *
	 */
	protected function _doFormatUserIdList(array $user_id_list):array {

		$output = [];
		foreach ($user_id_list as $v) {
			$output[] = intval($v);
		}
		return $output;
	}
}