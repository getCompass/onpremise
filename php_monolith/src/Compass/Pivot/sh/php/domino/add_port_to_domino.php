<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

if (Type_Script_InputParser::getArgumentValue("--help", Type_Script_InputParser::TYPE_NONE, false, false) === true) {

	console("Добавляет порты в доминошку");
	console("Параметры:");
	console("--domino-id - id доминошки");
	console("--mysql-user - пользователь для доступа к mysql");
	console("--mysql-pass - пароль для доступа к mysql");
	console("--start-port - начальный порт (включительно)");
	console("--end-port - конечный порт (включительно)");
	console("--type - тип портов (service, common, reserve)");
	exit;
}

$domino_id = Type_Script_InputParser::getArgumentValue("--domino-id", Type_Script_InputParser::TYPE_STRING, "", false);
if (mb_strlen($domino_id) < 1) {

	console("Введите id domino в которую хотите добавить порты");
	$domino_id = trim(readline());
	if (mb_strlen($domino_id) < 1) {

		console(redText("Не доступен пустой идентификатор"));
		exit(1);
	}
}

$mysql_user = Type_Script_InputParser::getArgumentValue("--mysql-user", Type_Script_InputParser::TYPE_STRING, "", false);
if (mb_strlen($mysql_user) < 1) {

	console("Введите mysql user который хотите задать для этой доминошки");
	$mysql_user = trim(readline());
	if (mb_strlen($mysql_user) < 1) {

		console(redText("Не доступен пустой пользователь"));
		exit(1);
	}
}

$mysql_pass = Type_Script_InputParser::getArgumentValue("--mysql-pass", Type_Script_InputParser::TYPE_STRING, "", false);
if (mb_strlen($mysql_pass) < 1) {

	console("Введите mysql pass который хотите задать для этой доминошки");
	$mysql_pass = trim(readline());
	if (mb_strlen($mysql_pass) < 1) {

		console(redText("Не доступен пустой пароль"));
		exit(1);
	}
}

$start_port = Type_Script_InputParser::getArgumentValue("--start-port", Type_Script_InputParser::TYPE_INT, false, false);
if ($start_port === false) {

	console("начальный порт (включительно)");
	$start_port = intval(trim(readline()));
}

if ($start_port < 1000) {

	console(redText("Нельзя использовать порт меньше 1000"));
	exit(1);
}

$end_port = Type_Script_InputParser::getArgumentValue("--end-port", Type_Script_InputParser::TYPE_INT, false, false);
if ($end_port === false) {

	console("конечный порт (включительно)");
	$end_port = intval(trim(readline()));
}

if ($end_port < $start_port) {

	console(redText("range задан не правильно"));
	exit(1);
}

$type = Type_Script_InputParser::getArgumentValue("--type", Type_Script_InputParser::TYPE_STRING, false, false);
if ($type === false) {

	console("тип портов (service, common, reserve)");
	$type = trim(readline());
}
if (!in_array($type, ["service", "common", "reserve"])) {

	console(redText("неизвестный тип портов"));
	exit(1);
}

$type_int = match ($type) {
	"service" => Domain_Domino_Entity_Port_Registry::TYPE_SERVICE,
	"common" => Domain_Domino_Entity_Port_Registry::TYPE_COMMON,
	"reserve" => Domain_Domino_Entity_Port_Registry::TYPE_RESERVE,
};

try {

	$domino_row = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($domino_id);
} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

	console(redText("Такая доминошка не существует"));
	exit(1);
}

$encrypted_mysql_user = \BaseFrame\System\Crypt::encrypt($mysql_user);
$encrypted_mysql_pass = \BaseFrame\System\Crypt::encrypt($mysql_pass);

for ($port = $start_port; $port <= $end_port; $port++) {
	Domain_Domino_Action_CreatePort::do($domino_id, $port, $type_int, $encrypted_mysql_user, $encrypted_mysql_pass);
}
