<?php

/**
 * Крон для повторной рассылки анонсов
 */

const IS_CRON = true;
require_once __DIR__ . "/../../../../../start.php";

$param = [
	"rabbit" => [
		"producer" => [
			"bot0",
		],
	],
];

$bot = new \Compass\Announcement\Cron_ResendAnnouncement($param);
$bot->start();