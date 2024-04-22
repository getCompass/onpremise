<?php

namespace Compass\Premise;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для установки версии сервера
 */
class Server_SetVersion {

	protected const _VERSION_REGEX = "/[1-9]+.[0-9]+.[0-9]+/";
	/**
	 * Запускаем работу скрипта
	 */
	public function run():void {

		$version = Type_Script_InputParser::getArgumentValue("--version");

		if (!preg_match(self::_VERSION_REGEX, $version, $matches)) {

			console(redText("Передана неверная версия"));
			exit(1);
		}

		$value = ["version" => $version];
		Domain_Config_Entity_Main::set(Domain_Config_Entity_Main::ONPREMISE_APP_VERSION, $value);
	}
}

try {
	(new Server_SetVersion())->run();
} catch (\Exception $e) {

	console($e->getMessage());
	console($e->getTraceAsString());
	console(redText("Не смогли зарегистировать сервер"));
	exit(1);
}
