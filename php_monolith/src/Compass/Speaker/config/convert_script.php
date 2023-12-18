<?php

const IMPORT_CONFIG_PATH = "/config/";
const COMPANY_CONFIG_PATH = "/app/api/conf/company/";

// что нам нужно из env для генерации world конфига
$env_keys = [
	"RABBIT_HOST",
	"RABBIT_PORT",
	"RABBIT_USER",
	"RABBIT_PASS",
	"MCACHE_HOST",
	"MCACHE_PORT",
];

convertWorldEnvConfig($env_keys);
convertCompanyJsonConfig();

// конвертим env переменные мира
function convertWorldEnvConfig(array $env_keys):void {

	$COMPANY_CONFIG = [];
	foreach ($env_keys as $env_key) {

		$env = getenv($env_key);

		// проверяем, если не массив - просто присвоили и пошли дальше
		if ($env[0] !== "[" || $env[strlen($env) - 1] !== "]") {

			$COMPANY_CONFIG["WORLD_".$env_key] = $env;
			continue;
		}

		// конвертим массив в нормальный вид
		$COMPANY_CONFIG["WORLD_".$env_key] = convertEnvArray($env);
	}

	// если ничего не спарсили - пустой конфиг получается
	if (count($COMPANY_CONFIG) < 1) {
		return;
	}

	$world_config = fopen(COMPANY_CONFIG_PATH. "world.php", "w+");

	fwrite($world_config, "<?php" . PHP_EOL ."return " . PHP_EOL . var_export($COMPANY_CONFIG, true) . ";");
	fclose($world_config);
}

// конвертим пришедший ассоциативный массив
function convertEnvArray(string $env):array {

	$output          = [];

	// отрезаем скобки и делим массив на элементы
	$env             = ltrim($env, "[");
	$env             = rtrim($env, "]");
	$env_array_items = explode(",", $env);

	// для каждого элемента смотрим его свойства
	foreach ($env_array_items as $env_array_item) {

		$item_output = [];

		// разделяем свойства
		$env_items = explode(";", $env_array_item);

		foreach ($env_items as $env_item) {

			// делим свойство на ключ-значение
			$key_value = explode(":", $env_item);

			// если передали некорректное значение - пропускаем
			if (count($key_value) !== 2) {
				continue;
			}

			// удаляем всякий мусор (пробелы, переходы на новую строку, табуляция)
			$key_value = array_map(function(string $value) {
				return str_replace(["\r\n", "\r", "\n", "\t"], "", $value);
			}, $key_value);

			// добавляем в массив
			$item_output[strtoupper($key_value[0])] = $key_value[1];
		}

		$output[] = $item_output;
	}

	return $output;
}

// конвертируем json конфиги компаний
function convertCompanyJsonConfig():void {

	$dir_contents        = scandir(IMPORT_CONFIG_PATH);
	$company_config_list = array_filter($dir_contents, function(string $file_name) {

		return str_contains($file_name, "company.json");
	});

	foreach ($company_config_list as $company_config_name) {

		// загружаем конфиг компании
		$company_config = json_decode(file_get_contents(IMPORT_CONFIG_PATH . "$company_config_name"), true);
		$company_constants = [];

		foreach ($company_config as $key => $config_item) {
			$company_constants["COMPANY_".strtoupper($key)] = $config_item;
		}

		// если ничего не спарсили - пустой конфиг получается
		if (count($company_constants) < 1) {
			return;
		}

		$new_company_config_name = explode(".", $company_config_name)[0];

		$output_file = fopen( COMPANY_CONFIG_PATH."{$new_company_config_name}.php", "w+");
		fwrite($output_file, "<?php". PHP_EOL . "return ". var_export($company_constants, TRUE).";");
		fclose($output_file);
	}
}