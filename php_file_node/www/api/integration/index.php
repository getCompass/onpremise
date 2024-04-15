<?php

// main
require_once __DIR__ . "/../../../start.php";

$payload = file_get_contents("php://input");
$data    = fromJson($payload);
if (!isset($data["method"]) ||
	!isset($data["json_params"])) {

	throw new \BaseFrame\Exception\Request\EndpointAccessDeniedException("no valid request");
}

// начинаем работу
$module = \Compass\FileNode\CURRENT_MODULE;
showAjax(Application\Entrypoint\Integration::processRequest($data["method"], $module, $data));
