<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1); // nosemgrep
set_time_limit(0);

if (Type_Script_InputParser::getArgumentValue("--help", Type_Script_InputParser::TYPE_NONE, false, false) === true) {

	console("Создает новую доминошку");
	console("Параметры:");
	console("--domino-id - id доминошки");
	console("--tier - тир для домино");
	console("--database-host - mysql хост для доминошки");
	console("--code-host - code хост для доминошки");
	console("--url - url для доминошки");
	console("--is-company-creating-allowed - Разрешено ли прогревать порты на этой домино для новых компаний");
	console("--go-database-controller-port - порт для go-database-controller");
	console("--go-database-controller-host - хост для go-database-controller");
	exit;
}

$domino_id = Type_Script_InputParser::getArgumentValue("--domino-id", Type_Script_InputParser::TYPE_STRING, "", false);
$domino_id = trim($domino_id);
if (mb_strlen($domino_id) < 1) {

	console("Введите id domino который хотите создать");
	$domino_id = trim(readline());
	if (mb_strlen($domino_id) < 1) {

		console(redText("Не доступен пустой идентификатор"));
		exit(1);
	}
}

$code_host = Type_Script_InputParser::getArgumentValue("--code-host", Type_Script_InputParser::TYPE_STRING, "", false);
$code_host = trim($code_host);
if (mb_strlen($code_host) < 1) {

	console("Введите code_host который хотите задать для этой доминошки");
	$code_host = trim(readline());
	if (mb_strlen($code_host) < 1) {

		console(redText("Не доступен пустой code_host"));
		exit(1);
	}
}

$go_database_controller_host = Type_Script_InputParser::getArgumentValue("--go-database-controller-host", Type_Script_InputParser::TYPE_STRING, "", false);
$go_database_controller_host = trim($go_database_controller_host);
$go_database_controller_port = Type_Script_InputParser::getArgumentValue("--go-database-controller-port", Type_Script_InputParser::TYPE_INT, false, false);
if ($go_database_controller_port === false) {

	console("Введите порт для доступа к database_controller");
	$go_database_controller_port = intval(trim(readline()));
}
$database_host = Type_Script_InputParser::getArgumentValue("--database-host", Type_Script_InputParser::TYPE_STRING, "", false);
$database_host = trim($database_host);
if ($database_host === "" && $go_database_controller_host === "") {

	console("Введите database_host который хотите задать для этой доминошки");
	$database_host = trim(readline());
	if ($database_host === "") {
		console(redText("Не доступен пустой database_host"));
	}
}

$is_company_creating_allowed = Type_Script_InputParser::getArgumentValue("--is-company-creating-allowed", Type_Script_InputParser::TYPE_INT, false, false);
if ($is_company_creating_allowed === false) {

	console("Можно ли на этой доминожке разворачивать компании 1/0");
	$is_company_creating_allowed = intval(trim(readline()));
}
if (!in_array($is_company_creating_allowed, [0, 1])) {

	console(redText("Передано не верное значение для прогрева"));
	exit(1);
}

$url = Type_Script_InputParser::getArgumentValue("--url", Type_Script_InputParser::TYPE_STRING, "", false);
$url = trim($url);
if (mb_strlen($url) < 1) {

	console("Введите url который хотите задать для этой доминошки");
	$url = trim(readline());
	if (mb_strlen($url) < 1) {

		console(redText("Не доступен пустой url"));
		exit(1);
	}
}

$tier = Type_Script_InputParser::getArgumentValue("--tier", Type_Script_InputParser::TYPE_INT, false, false);
if ($tier === false) {

	console("Введите tier для домино");
	$tier = intval(trim(readline()));
}

// проверить что такой domino_id уже занят
try {

	Gateway_Db_PivotCompanyService_DominoRegistry::getOne($domino_id);

	// если дошли до сюда то запись уже есть
	console(redText("Домино с таким id уже зарегистрирована"));
	exit(1);
} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
	// ничего не делаем
}

// создаем доминошку
try {

	Domain_Domino_Action_Create::do($domino_id, $tier, $is_company_creating_allowed, $go_database_controller_port, $url, $database_host, $code_host, $go_database_controller_host);
} catch (cs_NotCreatedDominoTable) {

	console(redText("Не удалось создать доминошку " . $domino_id));
	exit(1);
}