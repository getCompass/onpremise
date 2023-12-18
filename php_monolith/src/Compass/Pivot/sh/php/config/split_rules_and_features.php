<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

/**
 * Класс для разделения фич и их правил по разным конфигам (новый формат хранения)
 *
 * Class Split_Rules_And_Features
 */
class Split_Rules_And_Features {

	protected bool $is_dry_run = false;

	/**
	 * Определяем, сухой прогон или нет
	 *
	 */
	public function __construct(bool $is_dry_run = false) {

		$this->is_dry_run = $is_dry_run;
	}

	/**
	 * Разбиваем все конфиги с фичами на две части (фичи и правила раздельно)
	 *
	 * @throws cs_FeatureNotFound
	 * @throws cs_RuleAlreadyExists
	 * @throws cs_RuleNotFound
	 */
	public function splitConfigs(bool $is_init_config_php = false, array $all_configs = []):void {

		// конвертируем в новый формат все конфиги
		$platforms = Domain_User_Entity_Feature::PLATFORM_CONFIG_ALIAS;
		$rules     = [];
		foreach ($platforms as $platform => $platform_key) {

			$this->_printIfDryRun("Платформа " . $platform, true, true);

			// получаем конфиг старого формата
			if ($is_init_config_php === false && count($all_configs) === 0) {
				$config = Type_System_Config::init()->getConf($platform_key);
			} else {
				$config = $all_configs[$platform];
			}

			// разбиваем на две части
			[$config, $rules] = $this->_splitConfig($config, $rules);

			// сохраняем конфиг платформы
			if (!$this->is_dry_run) {

				if ($is_init_config_php === false) {
					Type_System_Config::init()->set($platform_key, $config);
				} else {
					console("$" . $platform . " = " . $this->_arrayExport($config));
				}
			}
		}

		// сохраняем правила
		if (!$this->is_dry_run) {

			if ($is_init_config_php === false) {
				Type_System_Config::init()->set(Domain_User_Entity_Feature::RULES_KEY, $rules);
			} else {
				console("\$rules = " . $this->_arrayExport($rules));
			}
		}
	}

	/**
	 * Разбиваем конфиг, формируем массивы
	 *
	 * @throws cs_FeatureNotFound
	 * @throws cs_RuleAlreadyExists
	 * @throws cs_RuleNotFound
	 */
	protected function _splitConfig(array $config, array $rules):array {

		// пробегаемся по каждой фиче
		foreach ($config as $feature_name => $feature) {

			$this->_printIfDryRun("Фича " . $feature_name, true);

			// обнуляем в конфиге у фичи все правила
			$config[$feature_name]["rules"] = [];

			// пробегаемся по каждому правилу
			foreach ($feature["rules"] as $rule_id => $rule) {

				// создаем правило
				[$rule_id, $rules] = Domain_User_Entity_Feature::addRule(
					$rules,
					(int) ($rule["type"] ?? 1),
					(float) ($rule["value"] ?? 0),
					$rule["users"] ?? [],
					(string) ($rule["version"] ?? ""),
					""
				);
				$this->_printIfDryRun("New rule_id: " . $rule_id . ": " . json_encode($rules[$rule_id]));

				// крепим правило к фиче
				$config = Domain_User_Entity_Feature::attachRuleToFeature($config, $feature_name, $rules, $rule_id, 0);
			}
			$this->_printIfDryRun("Правил перенесено: " . count($feature["rules"]));
			$this->_printIfDryRun("Список правил в фиче (rule_id => priority): " . json_encode($config[$feature_name]["rules"]));
		}

		return [$config, $rules];
	}

	/**
	 * Вывести сообщение, если сухой прогон
	 *
	 */
	protected function _printIfDryRun(string $text, bool $is_header = false, bool $big_header = false):void {

		if ($this->is_dry_run) {

			if ($is_header) {

				console("");
				console("--------------------------------------" . ($big_header ? "--------------------------------------" : ""));
				console($text);
				console("--------------------------------------" . ($big_header ? "--------------------------------------" : ""));
				console("");
			} else {
				console($text);
			}
		}
	}

	/**
	 * Получить массив в формате php
	 *
	 */
	protected function _arrayExport(array $array):string {

		// пллучаем массив php в строку
		$export = var_export($array, true);

		// замена старого стиля массивов на новый
		$patterns = [
			"/array \(/"                       => "[",
			"/^([ ]*)\)(,?)$/m"                => "$1]$2",
			"/=>[ ]?\n[ ]+\[/"                 => "=> [",
			"/([ ]*)(\'[^\']+\') => ([\[\'])/" => "$1$2 => $3",
			"/'/"                              => "\"",
		];
		$export = preg_replace(array_keys($patterns), array_values($patterns), $export);

		return $export;
	}
}

$is_dry_run = isDryRun();
$splitter   = new Split_Rules_And_Features($is_dry_run);

console("Что требуется сделать?");
console("1. обновить конфиги в базе данных");
console("2. обновить init_config.php конфиг");
console("3. перенести feature.php в базу данных");
$type = readline();

$all_configs = [
	"iphone"   => [],
	"electron" => [],
	"android"  => [],
];

if ($type == 1) {
	$splitter->splitConfigs();
} elseif ($type == 2) {

	if (count($all_configs["iphone"]) == 0 || count($all_configs["electron"]) == 0 || count($all_configs["android"]) == 0) {
		console("Требуется вставить старые конфиги в этот скрипт в массив \$all_configs");
	} else {
		$splitter->splitConfigs(true, $all_configs);
	}
} elseif ($type == 3) {

	$all_configs = [
		"iphone"   => getConfig("FEATURE_IOS"),
		"electron" => getConfig("FEATURE_ELECTRON"),
		"android"  => getConfig("FEATURE_ANDROID"),
	];
	$splitter->splitConfigs(false, $all_configs);
} else {
	console("Неизвестное действие");
}
