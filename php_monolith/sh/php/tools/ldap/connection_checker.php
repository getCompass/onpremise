<?php

use Compass\Federation\Domain_Ldap_Entity_Config;
use Compass\Federation\Domain_Ldap_Entity_Client_RequireCertStrategy;

require_once __DIR__ . "/../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

// получаем параметры из конфиг-файла
$server_host             = Domain_Ldap_Entity_Config::getServerHost();
$server_port             = Domain_Ldap_Entity_Config::getServerPort();
$search_account_username = Domain_Ldap_Entity_Config::getUserSearchAccountDn();
$search_account_password = Domain_Ldap_Entity_Config::getUserSearchAccountPassword();
$use_ssl                 = Domain_Ldap_Entity_Config::getUseSslFlag();
$require_cert_strategy   = Domain_Ldap_Entity_Client_RequireCertStrategy::convertStringToConst(Domain_Ldap_Entity_Config::getRequireCertStrategy());

// определяем протокол
$uri = $use_ssl ? "ldaps://$server_host:$server_port" : "ldap://$server_host:$server_port";

// устанавливаем соединение с сервером
// устанавливаем повышенный уровень дебага
// устанавливаем уровень верификации сертификата в зависимости от параметра из конфига
$conn = ldap_connect($uri);
ldap_set_option(null, LDAP_OPT_DEBUG_LEVEL, 7);
ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, $require_cert_strategy);

// если не удалось установить соединение
if (!$conn) {

	$err = sprintf("could not connect to ldap server [%s]", ldap_error($conn));
	console(redText($err));
	throw new Exception($err);
}

ldap_set_option($conn, LDAP_OPT_REFERRALS, false);
ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);

// пытаемся аутентифицировать клиента на сервере ldap
try {
	ldap_bind($conn, $search_account_username, $search_account_password);
} catch (Exception|Error $e) {

	// выбрасываем исключение, если это ldap ошибка
	$error_num = ldap_errno($conn);
	$err       = sprintf("error number %d, message: %s", $error_num, $e->getMessage());
	console(redText($err));
	throw $e;
}

console(greenText("success bind"));
