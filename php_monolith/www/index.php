<?php

// main
require_once __DIR__ . "/../start.php";

// стартуем сессию
\Compass\Pivot\Type_Session_Main::startSession();

$url_list = [
	"pivot"                      => PUBLIC_ENTRYPOINT_PIVOT . "/",
	"announcement"               => PUBLIC_ENTRYPOINT_ANNOUNCEMENT . "/",
	"billing"                    => PUBLIC_ENTRYPOINT_BILLING . "/",
	"partner"                    => PUBLIC_ENTRYPOINT_PARTNER . "/",
	"captcha"                    => PUBLIC_ENTRYPOINT_CAPTCHA . "/",
	"captcha_enterprise"         => PUBLIC_ENTRYPOINT_CAPTCHA_ENTERPRISE . "/",
	"yandex_captcha"             => PUBLIC_ENTRYPOINT_YANDEX_CAPTCHA . "/",
	"join"                       => PUBLIC_ENTRYPOINT_JOIN . "/",
	"join_variety"               => array_map(static fn(string $e) => "$e/", array_filter(PUBLIC_ENTRYPOINT_JOIN_VARIETY)),
	"invite"                     => PUBLIC_ENTRYPOINT_INVITE . "/",
	"premise"                    => PUBLIC_ENTRYPOINT_PREMISE . "/",
	"solution"                   => "",
	"video_conference"           => PUBLIC_ENTRYPOINT_VIDEO_CONFERENCE . "/",
	"video_conference_node_list" => VIDEO_CONFERENCE_NODE_LIST,
	"electron_update"            => PUBLIC_ENTRYPOINT_ELECTRON_UPDATE . "/",
	"electron_update30"          => PUBLIC_ENTRYPOINT_ELECTRON_UPDATE30 . "/",
	"electron_update_cdn"        => PUBLIC_ENTRYPOINT_ELECTRON_UPDATE_CDN . "/",
];

foreach ($url_list as &$url) {

	if ($url === "/") {
		$url = "";
	}
}

$response = [
	"start_url"         => (string) PUBLIC_ENTRYPOINT_PIVOT . "/",
	"connect_check_url" => (string) PUBLIC_CONNECT_CHECK_URL . "/",
	"version"           => ONPREMISE_VERSION,
	"url_list"          => (object) $url_list,
	"service_data"      => (object) [
		"dsn" => (object) [
			"electron" => SENTRY_DSN_KEY_ELECTRON,
			"android"  => SENTRY_DSN_KEY_ANDROID,
			"ios"      => SENTRY_DSN_KEY_IOS,
		],
	],
];


$answer = [
	"status"   => "ok",
	"response" => $response,
];

// проверим данные авторизации
$auth_data = \BaseFrame\Http\Authorization\Data::inst();

// если данные авторизации не менялись, то ничего не делаем
if ($auth_data->hasChanges()) {

	$answer["actions"][] = [
		"type" => "authorization",
		"data" => $auth_data->get(),
	];
}

// отдаем ok, что сессия установлена
showAjax($answer);