<?php

namespace Compass\Conversation;

/**
 * utils методы для url preview
 */
class Type_Preview_Utils {

	// регулярное выражение для поиска почты
	public const DOMAIN_REGULAR_EXPRESSION = "+(?:[A-Z]{1,6}|com|org|net|edu|gov|mil|biz|info|mobi|name|aero|asia|jobs|museum|ru|uk)";
	public const EMAIL_REGULAR_EXPRESSION  = "/[a-zA-Z0-9!#$%&'*+\=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)" . self::DOMAIN_REGULAR_EXPRESSION . "*/";

	// формируем список ссылок
	public static function makeLinkList(string $original_url, string $redirect_url, bool $is_add_protocol = true):array {

		return [
			"original_link" => $original_url,
			"redirect_link" => $is_add_protocol ? self::addProtocol($redirect_url) : $redirect_url,
		];
	}

	// добавляем протокол к ссылке
	public static function addProtocol(string $url):string {

		// если у переданной ссылки не указан http(s) - используем http по умолчанию
		if (!preg_match("/^(http|https):\\/\\//ui", $url)) {
			$url = "http://" . $url;
		}

		return $url;
	}

	// кодируем ссылку
	public static function encodeUrl(string $url):string {

		// разбиваем ссылку
		$parsed_url = parse_url($url);

		// собираем ссылку, если нету составляющих заменяем пустотой
		$scheme   = isset($parsed_url["scheme"]) ? mb_strtolower($parsed_url["scheme"]) : "http";
		$host     = isset($parsed_url["host"]) ? $parsed_url["host"] : "";
		$port     = isset($parsed_url["port"]) ? ":" . $parsed_url["port"] : "";
		$user     = isset($parsed_url["user"]) ? $parsed_url["user"] : "";
		$pass     = isset($parsed_url["pass"]) ? ":" . $parsed_url["pass"] : "";
		$pass     = ($user || $pass) ? "$pass@" : "";
		$path     = isset($parsed_url["path"]) ? $parsed_url["path"] : "";
		$query    = isset($parsed_url["query"]) ? "?" . $parsed_url["query"] : "";
		$fragment = isset($parsed_url["fragment"]) ? "#" . $parsed_url["fragment"] : "";

		// кодируем запрос
		$query = rawurlencode($query);

		return "$scheme://$user$pass$host$port$path$query$fragment";
	}

	// получаем protocol
	public static function getProtocolByUrl(string $url):string {

		// разбиваем ссылку
		$parsed_url = parse_url($url);

		// получаем протокол
		$scheme = isset($parsed_url["scheme"]) ? mb_strtolower($parsed_url["scheme"]) : "http";

		return $scheme;
	}

	// проверяем что пришла почта
	public static function isEmail(string $text):bool {

		$result = preg_match(self::EMAIL_REGULAR_EXPRESSION, $text);

		// если не нашли совпадение
		if ($result < 1) {
			return false;
		}

		return true;
	}
}