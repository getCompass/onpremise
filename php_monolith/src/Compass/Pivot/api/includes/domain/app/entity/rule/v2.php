<?php

namespace Compass\Pivot;

/**
 * Правила второй версии
 */
class Domain_App_Entity_Rule_V2 extends Domain_App_Entity_Rule {

	protected const _RULE_VERSION = 2;

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

	// местоположение инициализирующего конфига правил
	protected const _INITIALIZATION_CONFIG_PATH = PIVOT_MODULE_ROOT . "/conf/rule_v2.json";

	/** Инициализировать конфиг
	 *
	 * @param bool $need_force_update
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function initializeConfig(bool $need_force_update = false):void {

		$config = Type_System_Config::init()->getConf($this->getConfigKey());

		if (count($config) > 0 && !$need_force_update) {
			return;
		}

		if (!file_exists(self::_INITIALIZATION_CONFIG_PATH)) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("cant find initialization config");
		}

		$output_config        = [];
		$config_file_contents = file_get_contents(self::_INITIALIZATION_CONFIG_PATH);

		$initialization_config = fromJson($config_file_contents);

		// если json не смогли спарсить - значит фиговый конфиг инициализации - выбрасываем исключение
		if (count($initialization_config) < 1) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("invalid initialization rule config!");
		}

		// для каждой фичи находим версию для платформы и типа приложения
		foreach ($initialization_config as $rule_name => $rule) {

			// если в конфиге нет обязательных полей - значит конфиг неправильный, выкидываем ошибку и прекращаем его формирование
			if (!isset($rule["type"]) || !isset($rule["values"]) || !isset($rule["priority"]) || !isset($rule["restrictions"])) {
				throw new \BaseFrame\Exception\Domain\ParseFatalException("invalid initialization rule config!");
			}

			// добавляем в конфиг
			$output_config[$rule_name] = $rule;
		}

		// записываем конфиг в базу
		Gateway_Db_PivotData_PivotConfig::set($this->getConfigKey(), $output_config);
	}

	/**
	 * Получить конфиг с правилами
	 *
	 * @param bool $preserve_keys
	 *
	 * @return array
	 * @throws Domain_App_Exception_Rule_InvalidValue
	 */
	public function getConfig(bool $preserve_keys = true):array {

		$output = [];
		$config = Type_System_Config::init()->getConf($this->getConfigKey());
		foreach ($config as $rule_name => $rule) {

			$output[$rule_name] = Struct_Rule_V2::fromArray([
				"name"         => $rule_name,
				"type"         => $rule["type"],
				"priority"     => $rule["priority"],
				"restrictions" => $rule["restrictions"],
				"values"       => $rule["values"],
			])->toArrayWithName();
		}

		if (!$preserve_keys) {
			return array_values($output);
		}

		return $output;
	}

	/**
	 * Добавить новое правило
	 *
	 * @param string $rule_name
	 * @param int    $type
	 * @param int    $priority
	 * @param array  $restrictions
	 * @param array  $values
	 *
	 * @return array
	 * @throws Domain_App_Exception_Rule_InvalidName
	 * @throws Domain_App_Exception_Rule_InvalidValue
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function add(string $rule_name, int $type, int $priority, array $restrictions, array $values):array {

		$this->_assertValidName($rule_name);

		$config = Type_System_Config::init()->getConf($this->getConfigKey());

		// проверяем, что валидный тип
		$this->_assertValidType($type);

		$rule = Struct_Rule_V2::fromArray([
			"name"         => trim(mb_strtolower($rule_name)),
			"type"         => $type,
			"priority"     => $priority,
			"restrictions" => $restrictions,
			"values"       => $values,
		]);

		$config[$rule_name] = $rule->toArray();
		Type_System_Config::init()->set($this->getConfigKey(), $config);

		return $rule->toArrayWithName();
	}

	/**
	 * Изменить правило
	 *
	 * @param string $rule_name
	 * @param array  $restrictions
	 * @param array  $values
	 * @param array  $set
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @long
	 */
	public function edit(string $rule_name, array $restrictions, array $values, array $set):array {

		$new_rule_name = "";
		$config        = Type_System_Config::init()->getConf($this->getConfigKey());

		// если правила не существует - выбрасываем ошибку
		if (!isset($config[$rule_name])) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("rule doesnt exist");
		}

		// для каждого значения в переданном массиве
		foreach ($set as $key => $value) {

			// пропускаем массивы, они должны обрабатываться отдельно
			if (gettype($value) == "array") {
				continue;
			}

			// если пытаемся изменить имя фичи - то запоминаем его, чтобы потом узнать, можем ли мы такое сделать
			if ($key === "name" && $rule_name !== $value && $value !== "") {

				$this->_assertValidName($value);

				$new_rule_name = trim(mb_strtolower($value));
				continue;
			}

			// устанавливаем значение
			$config[$rule_name][$key] = $value;
		}

		// меняем ограничение и значение правила
		$restrictions = $this->_editRestrictions($config[$rule_name]["restrictions"], $restrictions);
		$values       = $this->_editValues($config[$rule_name]["values"], $values);

		// если пытаемся изменить имя фичи
		if ($new_rule_name != "") {

			$config    = $this->_changeName($config, $rule_name, $new_rule_name);
			$rule_name = $new_rule_name;
		}

		// форматируем объект, исключая левак и приводя значения к нужным типам
		$rule = Struct_Rule_V2::init($rule_name, $config[$rule_name]["type"], $config[$rule_name]["priority"], $restrictions, $values);

		// формируем в массив для конфига
		$config[$rule_name] = $rule->toArray();

		// записываем конфиг
		Type_System_Config::init()->set($this->getConfigKey(), $config);

		return $rule->toArrayWithName();
	}

	/**
	 * Изменить ограничения
	 *
	 * @param array      $restrictions
	 * @param array|null $set
	 *
	 * @return Struct_Rule_Main_Restrictions
	 */
	protected function _editRestrictions(array $restrictions, ?array $set):Struct_Rule_Main_Restrictions {

		if (!$set) {
			return Struct_Rule_Main_Restrictions::fromArray($restrictions);
		}

		foreach ($set as $key => $value) {
			$restrictions[$key] = $value;
		}

		return Struct_Rule_Main_Restrictions::fromArray($restrictions);
	}

	/**
	 * Изменить ограничения
	 *
	 * @param array      $values
	 * @param array|null $set
	 *
	 * @return Struct_Rule_V2_Values
	 */
	protected function _editValues(array $values, ?array $set):Struct_Rule_V2_Values {

		if (!$set) {
			return Struct_Rule_V2_Values::fromArray($values);
		}

		foreach ($set as $key => $value) {
			$values[$key] = $value;
		}

		return Struct_Rule_V2_Values::fromArray($values);
	}

	/**
	 * Изменить имя фичи
	 *
	 * @param array  $config
	 * @param string $rule_name
	 * @param string $new_rule_name
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected function _changeName(array $config, string $rule_name, string $new_rule_name):array {

		// если уже существует такая фича - выбрасываем исключение
		if (isset($config[$new_rule_name])) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("rule with this name already exists");
		}

		// перекладываем фичу по новому имени
		$config[$new_rule_name] = $config[$rule_name];
		unset($config[$rule_name]);

		return $config;
	}

	/**
	 * Удалить правило
	 *
	 * @param string $rule_name
	 *
	 * @return void
	 */
	public function delete(string $rule_name):void {

		$config = Type_System_Config::init()->getConf($this->getConfigKey());

		// приводим к utf-8
		$rule_name = urldecode($rule_name);

		// если фичи не существует - значит и удалять нечего
		if (!isset($config[$rule_name])) {
			return;
		}

		// удаляем фичу
		unset($config[$rule_name]);

		// записываем конфиг
		Type_System_Config::init()->set($this->getConfigKey(), $config);
	}

	/**
	 * Надо ли применять правило или нет
	 *
	 * @param Struct_Rule_V2 $rule
	 * @param int|null       $max_priority
	 * @param int|null       $min_type
	 * @param int            $user_id
	 * @param string         $app_version
	 *
	 * @return bool
	 */

	public static function isNeedApplyRule(Struct_Rule_V2 $rule,
							   ?int           $max_priority, ?int $min_type, int $user_id, string $app_version):bool {

		// если нет поля type
		if (!isset($rule->type)) {
			return false;
		}

		// если приоритет меньше сразу отдаем false
		if (!is_null($max_priority) && $rule->priority < $max_priority) {
			return false;
		}
		// если одинаковый приоритет, сравниваем типы
		if (!is_null($max_priority) && $rule->priority == $max_priority && $rule->type > $min_type) {
			return false;
		}

		return match ($rule->type) {
			self::RULE_TYPE_USERS                                       =>
			in_array($user_id, $rule->restrictions->user_list),
			self::RULE_TYPE_APP_VERSION_GREATER_OR_EQUAL_THAN           =>
			version_compare($app_version, $rule->restrictions->app_version, ">="),
			self::RULE_TYPE_APP_VERSION_LOWER_OR_EQUAL_THAN             =>
			version_compare($app_version, $rule->restrictions->app_version, "<="),
			self::RULE_TYPE_USERS_AND_APP_VERSION_GREATER_OR_EQUAL_THAN =>
				in_array($user_id, $rule->restrictions->user_list) && version_compare($app_version, $rule->restrictions->app_version, ">="),
			self::RULE_TYPE_USERS_AND_APP_VERSION_LOWER_OR_EQUAL_THAN   =>
				in_array($user_id, $rule->restrictions->user_list) && version_compare($app_version, $rule->restrictions->app_version, "<="),
			default                                                     => false,
		};
	}

	/**
	 * доступен ли такой тип правила
	 *
	 */
	public static function isAllowRuleType(int $rule_type):bool {

		return in_array($rule_type, self::_ALLOW_RULE_TYPE_LIST);
	}

	/**
	 * Проверяем, что валидное имя
	 *
	 * @param string $rule_name
	 *
	 * @return void
	 * @throws Domain_App_Exception_Rule_InvalidName
	 */
	protected function _assertValidName(string $rule_name):void {

		if (!preg_match("/^[a-zA-Z0-9-_]+$/", $rule_name)) {
			throw new Domain_App_Exception_Rule_InvalidName("passed invalid name");
		}
	}

	/**
	 * Передан ли валидный тип
	 *
	 * @param int $type
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected function _assertValidType(int $type):void {

		if (!in_array($type, self::_ALLOW_RULE_TYPE_LIST)) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("unknown rule type");
		}
	}
}