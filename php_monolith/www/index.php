<?php

// main
require_once __DIR__ . "/../start.php";

// стартуем сессию
\Compass\Pivot\Type_Session_Main::startSession();

$url_list = [
	"pivot"        => PUBLIC_ENTRYPOINT_PIVOT . "/",
	"announcement" => PUBLIC_ENTRYPOINT_ANNOUNCEMENT . "/",
	"billing"      => PUBLIC_ENTRYPOINT_BILLING . "/",
	"partner"      => PUBLIC_ENTRYPOINT_PARTNER . "/",
	"captcha"      => PUBLIC_ENTRYPOINT_CAPTCHA . "/",
	"join"         => PUBLIC_ENTRYPOINT_JOIN . "/",
	"invite"       => PUBLIC_ENTRYPOINT_INVITE . "/",
	"solution"     => PUBLIC_ENTRYPOINT_SOLUTION . "/",
];

foreach ($url_list as &$url) {

	if ($url === "/") {
		$url = "";
	}
}

// отдаем ok, что сесия установлена
showAjax([
	"status"   => "ok",
	"response" => [
		"start_url" => (string) PUBLIC_ENTRYPOINT_START . "/",
		"url_list"  => (object) $url_list,
	],
]);
