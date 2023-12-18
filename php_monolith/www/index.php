<?php

// main
require_once __DIR__ . "/../start.php";

// стартуем сессию
\Compass\Pivot\Type_Session_Main::startSession();

// отдаем ok, что сесия установлена
showAjax([
	"status"   => "ok",
	"response" => [
		"start_url"   => PIVOT_PROTOCOL . "://" . PIVOT_DOMAIN . "/",
	],
]);
