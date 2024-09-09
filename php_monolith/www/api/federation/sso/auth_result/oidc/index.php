<?php

// main
require_once __DIR__ . "/../../../../../../start.php";

// если пришла ошибка
if (isset($_REQUEST["error"])) {
	\Compass\Federation\Domain_Oidc_Scenario_Endpoint_Oidc::onError($_REQUEST["error"], $_REQUEST["error_description"]);
}

// если не пришел код или state
if (!isset($_REQUEST["code"]) || !isset($_REQUEST["state"])) {
	throw new \BaseFrame\Exception\Request\EndpointAccessDeniedException("unexpected behaviour");
}

$redirect_url = \Compass\Federation\Domain_Oidc_Scenario_Endpoint_Oidc::onReceiveAuthorizationCode($_REQUEST["code"], urldecode($_REQUEST["state"]));

// редиректим пользователя
header("Location: $redirect_url");