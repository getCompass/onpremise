<?php

// скрипт для добавления предопределенных хостов на домино
namespace Compass\Pivot;

use BaseFrame\Exception\Gateway\RowNotFoundException;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1); // nosemgrep
set_time_limit(0);

if (Type_Script_InputParser::getArgumentValue("--help", Type_Script_InputParser::TYPE_NONE, false, false) === true) {

	console("Добавляет порты в доминошку");
	console("Параметры:");
	console("--domino-id - id доминошки");
	console("--host-list - список хостов в формате hostname:port");
	console("--type - тип портов (service, common, reserve)");
	console("--mysql-user - пользователь для доступа к mysql");
	console("--mysql-pass - пароль для доступа к mysql");
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

$host_list = Type_Script_InputParser::getArgumentValue("--host-list", Type_Script_InputParser::TYPE_ARRAY, false, false);
if ($host_list === false) {
	console(redText("Требуется добавить хотя бы один хост"));
	exit(1);
}

$create_host_list = [];

foreach ($host_list as $host) {

	$exploded_host = explode(":", $host);
	if (count($exploded_host) !== 2 || !ctype_digit($exploded_host[1])) {
		console(redText("Хост в списке задан неверно, используйте формат HOSTNAME:PORT"));
	}

	$hostname           = $exploded_host[0];
	$port               = (int) $exploded_host[1];
	$create_host_list[] = [
		"hostname" => $hostname,
		"port"     => $port,
	];
}

try {

	Gateway_Db_PivotCompanyService_DominoRegistry::getOne($domino_id);
} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

	console(redText("Такая доминошка не существует"));
	exit(1);
}

$encrypted_mysql_user = \BaseFrame\System\Crypt::encrypt($mysql_user);
$encrypted_mysql_pass = \BaseFrame\System\Crypt::encrypt($mysql_pass);

foreach ($create_host_list as $host) {

	// если передали существующий порт, то не надо его создавать
	try {
		Gateway_Db_PivotCompanyService_PortRegistry::getOne($domino_id, $host["port"], $host["hostname"]);
	} catch (RowNotFoundException) {

		// если на нашли - делаем
		Domain_Domino_Action_CreatePort::do(
			$domino_id, $host["port"], $host["hostname"], $type_int, $encrypted_mysql_user, $encrypted_mysql_pass);
	}
}