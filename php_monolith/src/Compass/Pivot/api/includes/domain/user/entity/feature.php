<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * класс для разруливания констант 1/0 по заданным правилам в конфиге feature.php для каждой платформы
 * сделана версионность для того чтобы возможно было переехат на другой формат конфигов
 */
class Domain_User_Entity_Feature {

	// ключ общих правил
	public const RULES_KEY = "FEATURE_V1_RULES";

	public const COMPASS_POSTFIX = "_compass";
	public const COMTEAM_POSTFIX = "_comteam";

	// массив платформ и их конфига с фичей
	public const PLATFORM_CONFIG_ALIAS = [
		Type_Api_Platform::PLATFORM_ELECTRON . self::COMPASS_POSTFIX => "FEATURE_V1_ELECTRON_COMPASS",
		Type_Api_Platform::PLATFORM_ANDROID . self::COMPASS_POSTFIX  => "FEATURE_V1_ANDROID_COMPASS",
		Type_Api_Platform::PLATFORM_IOS . self::COMPASS_POSTFIX      => "FEATURE_V1_IOS_COMPASS",
		Type_Api_Platform::PLATFORM_IPAD . self::COMPASS_POSTFIX     => "FEATURE_V1_IPAD_COMPASS",
		Type_Api_Platform::PLATFORM_ELECTRON . self::COMTEAM_POSTFIX => "FEATURE_V1_ELECTRON_COMTEAM",
		Type_Api_Platform::PLATFORM_ANDROID . self::COMTEAM_POSTFIX  => "FEATURE_V1_ANDROID_COMTEAM",
		Type_Api_Platform::PLATFORM_IOS . self::COMTEAM_POSTFIX      => "FEATURE_V1_IOS_COMTEAM",
		Type_Api_Platform::PLATFORM_IPAD . self::COMTEAM_POSTFIX     => "FEATURE_V1_IPAD_COMTEAM",
	];

	// типы возможных правил в конфиге
	// чем меньше число - тем больше приоритет
	public const RULE_TYPE_USERS                                       = 1; // список юзеров к которым применяется значение (самый приоритетный)
	public const RULE_TYPE_APP_VERSION_GREATER_OR_EQUAL_THAN           = 2; // применяется если версия приложения юзера >= чем заданная
	public const RULE_TYPE_APP_VERSION_LOWER_OR_EQUAL_THAN             = 3; // применяется если версия приложения юзера <= чем заданная
	public const RULE_TYPE_USERS_AND_APP_VERSION_GREATER_OR_EQUAL_THAN = 4; // применяется если список юзеров и версия приложения юзера >= чем заданная
	public const RULE_TYPE_USERS_AND_APP_VERSION_LOWER_OR_EQUAL_THAN   = 5; // применяется если список юзеров и версия приложения юзера <= чем заданная

	// список доступных правил
	protected const _ALLOW_RULE_TYPE_LIST = [
		self::RULE_TYPE_USERS,
		self::RULE_TYPE_APP_VERSION_GREATER_OR_EQUAL_THAN,
		self::RULE_TYPE_APP_VERSION_LOWER_OR_EQUAL_THAN,
		self::RULE_TYPE_USERS_AND_APP_VERSION_GREATER_OR_EQUAL_THAN,
		self::RULE_TYPE_USERS_AND_APP_VERSION_LOWER_OR_EQUAL_THAN,
	];

	// структура фичи
	protected const _DEFAULT_FEATURE_STRUCT = [
		"value" => 0,
		"rules" => [],
	];

	/**
	 * Основная функция которая возвращает конфиг для платформы
	 *
	 * @throws \paramException
	 */
	public static function getAppConfigForUser(string $platform, string $app_name, int $user_id, string $app_version):array {

		$config_key = $platform . "_" . $app_name;
		if (!isset(Domain_User_Entity_Feature::PLATFORM_CONFIG_ALIAS[$config_key])) {
			throw new ParamException("Undefined platform or app name");
		}

		$config = Type_System_Config::init()->getConf(Domain_User_Entity_Feature::PLATFORM_CONFIG_ALIAS[$config_key]);
		$rules  = Type_System_Config::init()->getConf(Domain_User_Entity_Feature::RULES_KEY);

		// проходимся конфигу фич и наполняем ответ
		$output = [];
		foreach ($config as $k => $v) {
			$output[$k] = self::_getFeatureValue($rules, $v, $user_id, $app_version);
		}
		return $output;
	}

	/**
	 * значение фичи
	 *
	 * @mixed - может вернуть что угодно
	 */
	protected static function _getFeatureValue(array $rules, array $feature, int $user_id, string $app_version):mixed {

		// дефолтное значение
		$value        = $feature["value"];
		$max_priority = null;
		$min_type     = null;

		// проходимся по правилам
		foreach ($feature["rules"] as $rule_id => $priority) {

			// получаем правило по id, если его нет, пропускаем, чтобы ничего не ломать
			if (!isset($rules[$rule_id])) {
				continue;
			}
			$rule = $rules[$rule_id];

			// если правило надо применить - применяем
			if (self::_isNeedApplyRule($rule, $priority, $max_priority, $min_type, $user_id, $app_version)) {

				$value        = $rule["value"];
				$max_priority = $priority;
				$min_type     = $rule["type"];
			}
		}

		// отдаем значение
		return $value;
	}

	/**
	 * говорит надо ли применять правило или нет
	 *
	 */
	protected static function _isNeedApplyRule(array $rule, int $priority, ?int $max_priority, ?int $min_type, int $user_id, string $app_version):bool {

		// если нет поля type - сразу false (на случай если конфиг старого формата)
		if (!isset($rule["type"])) {
			return false;
		}

		// если приоритет меньше сразу отдаем false
		if (!is_null($max_priority) && $priority < $max_priority) {
			return false;
		}
		// если одинаковый приоритет, сравниваем типы
		if (!is_null($max_priority) && $priority == $max_priority && $rule["type"] > $min_type) {
			return false;
		}

		return match ($rule["type"]) {
			self::RULE_TYPE_USERS                                       => in_array($user_id, $rule["users"]),
			self::RULE_TYPE_APP_VERSION_GREATER_OR_EQUAL_THAN           => version_compare($app_version, $rule["version"], ">="),
			self::RULE_TYPE_APP_VERSION_LOWER_OR_EQUAL_THAN             => version_compare($app_version, $rule["version"], "<="),
			self::RULE_TYPE_USERS_AND_APP_VERSION_GREATER_OR_EQUAL_THAN =>
				in_array($user_id, $rule["users"]) && version_compare($app_version, $rule["version"], ">="),
			self::RULE_TYPE_USERS_AND_APP_VERSION_LOWER_OR_EQUAL_THAN   =>
				in_array($user_id, $rule["users"]) && version_compare($app_version, $rule["version"], "<="),
			default                                                     => false,
		};
	}

	/**
	 * добавить/изменить константу
	 *
	 */
	public static function addOrEdit(array $config, string $feature_name, float $value):array {

		$feature = self::_DEFAULT_FEATURE_STRUCT;

		// если фича есть в конфиги то получаем ее чтобы не сбить правила
		if (isset($config[$feature_name])) {
			$feature = $config[$feature_name];
		}

		// устанавливаем значения и обновляем конфиг
		$feature["value"]      = $value;
		$config[$feature_name] = $feature;

		return $config;
	}

	/**
	 * удаляем константу
	 *
	 */
	public static function delete(array $config, string $feature_name):array {

		// если фичи нет в конфиги то отдаем его сразу
		if (!isset($config[$feature_name])) {
			return $config;
		}

		unset($config[$feature_name]);

		return $config;
	}

	/**
	 * изменить имя константе
	 *
	 * @throws cs_FeatureNotFound
	 */
	public static function changeFeatureName(array $config, string $feature_name, string $new_feature_name):array {

		// если фича есть в конфиги то получаем ее чтобы не сбить правила
		if (!isset($config[$feature_name])) {
			throw new cs_FeatureNotFound();
		}

		// если имена и так одинаковые то просто выходим
		if ($feature_name == $new_feature_name) {
			return $config;
		}

		// устанавливаем значения и обновляем конфиг
		$config[$new_feature_name] = $config[$feature_name];
		unset($config[$feature_name]);

		return $config;
	}

	/**
	 * добавляем новое правило
	 *
	 * @throws cs_RuleAlreadyExists
	 */
	public static function addRule(array $rules, int $rule_type, float $rule_value, array $user_id_list, string $version, string $rule_name):array {

		// получаем последний id и делаем проверки
		$last_rule_id = 0;
		foreach ($rules as $rule_id => $rule) {

			// проверяем наличие правила с таким именем, если устанавливается имя
			if (mb_strlen($rule_name) != 0 && isset($rule["name"]) && $rule["name"] == $rule_name) {
				throw new cs_RuleAlreadyExists();
			}
			$last_rule_id = max($last_rule_id, $rule_id);
		}

		// создаем правило
		$rule_id         = $last_rule_id + 1;
		$rules[$rule_id] = [
			"type"    => $rule_type,
			"value"   => $rule_value,
			"users"   => $user_id_list,
			"version" => $version,
			"name"    => $rule_name,
		];

		return [$rule_id, $rules];
	}

	/**
	 * удаляем правило новое правило
	 *
	 * @throws cs_RuleNotFound
	 */
	public static function deleteRule(array $all_configs, array $rules, int $rule_id):array {

		// проверяем наличие правила в списке всех правил
		if (!isset($rules[$rule_id])) {
			throw new cs_RuleNotFound();
		}

		// удаляем правило
		unset($rules[$rule_id]);

		// удаляем это правило из всех фич
		foreach ($all_configs as $platform => $config) {

			foreach ($config as $feature_name => $feature) {

				if (isset($feature["rules"][$rule_id])) {
					unset($all_configs[$platform][$feature_name]["rules"][$rule_id]);
				}
			}
		}

		return [$all_configs, $rules];
	}

	/**
	 * устанавливаем правило
	 *
	 * @throws cs_RuleAlreadyExists
	 * @throws cs_RuleConvertingToUnnamed
	 * @throws cs_RuleNotFound
	 */
	public static function editRule(array $rules, int $rule_id, int $rule_type, float $rule_value, array $user_id_list, string $version = "", string $rule_name = ""):array {

		if (!isset($rules[$rule_id])) {
			throw new cs_RuleNotFound();
		}
		// проверяем, что мы не делаем именное правило безимянным
		if ($rules[$rule_id]["name"] !== "" && $rule_name == "") {
			throw new cs_RuleConvertingToUnnamed();
		}
		// проверяем, что не существует другого правила с таким именем
		if ($rule_name != "" && $rule_name != $rules[$rule_id]["name"]) {

			foreach ($rules as $rule) {

				if ($rule["name"] == $rule_name) {
					throw new cs_RuleAlreadyExists();
				}
			}
		}

		$rules[$rule_id] = [
			"type"    => $rule_type,
			"value"   => $rule_value,
			"users"   => $user_id_list,
			"version" => $version,
			"name"    => $rule_name,
		];

		return $rules;
	}

	/**
	 * крепим правило к фиче
	 *
	 * @throws cs_FeatureNotFound
	 * @throws cs_RuleNotFound
	 */
	public static function attachRuleToFeature(array $config, string $feature_name, array $rules, int $rule_id, int $priority):array {

		if (!isset($config[$feature_name])) {
			throw new cs_FeatureNotFound();
		}
		if (!isset($rules[$rule_id])) {
			throw new cs_RuleNotFound();
		}

		$config[$feature_name]["rules"][$rule_id] = $priority;

		return $config;
	}

	/**
	 * открепляем правило от фичи
	 *
	 * @throws cs_FeatureNotFound
	 * @throws cs_RuleNotFound
	 */
	public static function detachRuleFromFeature(array $config, string $feature_name, array $rules, int $rule_id):array {

		if (!isset($config[$feature_name])) {
			throw new cs_FeatureNotFound();
		}
		if (!isset($rules[$rule_id])) {
			throw new cs_RuleNotFound();
		}
		if (!isset($config[$feature_name]["rules"][$rule_id])) {
			throw new cs_RuleNotFound();
		}

		unset($config[$feature_name]["rules"][$rule_id]);

		return $config;
	}

	/**
	 * доступен ли такой тип правила
	 *
	 */
	public static function isAllowRuleType(int $rule_type):bool {

		return in_array($rule_type, self::_ALLOW_RULE_TYPE_LIST);
	}
}
