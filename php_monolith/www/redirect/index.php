<?php

use Compass\Conversation\Type_Preview_Config;

require_once __DIR__ . "/../../start.php";

// проверяем что передали ссылку
if (!isset($_SERVER["QUERY_STRING"])) {

	header("Location: https://getcompass.com");
	exit();
}

// получаем от сервера ссылку и исправляем ее
$link = preg_replace("/^link=/", "", $_SERVER["QUERY_STRING"]);

// раскодируем ссылку на случай, если она пришла закодированной
$link = rawurldecode($link);

// если в ссылке есть символы переноса строки - убираем их
$link = str_replace([PHP_EOL, "\r", "\u{2028}", "\u{2029}", "\u{000A}", "\u{000D}", "\u{0085}"], "", $link);

// если передали - проверяем что она не в ЧС
$link = formatString($link);

// если у переданной ссылки не указан http(s) - используем http по умолчанию
if (!preg_match("/^(http|https):\\/\\//u", $link)) {
	$link = "http://" . $link;
}

$domain = parse_url($link, PHP_URL_HOST);

if (is_null($domain)) {

	header("Location: https://getcompass.com");
	exit();
}

if (Type_Preview_Config::isDomainInRedirectBlackList($domain)) {

	include "error.tpl";
	exit();
}

header("Location: " . $link);
exit();