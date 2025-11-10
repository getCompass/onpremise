<?php

namespace Compass\Pivot;

/**
 * Класс для валидации сущности www
 */
class Type_Www_Validator {

	protected const _BOT_LIST = [
		// поисковые роботы
		"googlebot", "bingbot", "yandexbot", "baiduspider", "duckduckbot", "slurp", "sogou", "exabot", "facebot", "ahrefs", "semrush",
		// веб-сканеры
		"curl", "wget", "python", "java", "php", "perl", "node", "ruby", "go-http",
		// вредоносные
		"scraper", "spammer", "hacker", "brutus", "sqlmap", "nikto", "zgrab",
		// общие паттерны
		"bot", "spider", "crawl", "fetch", "headless", "phantom", "selenium", "puppeteer", "httpclient", "library"
	];

	// максимальная длина user-agent для бота
	protected const _BOT_USER_AGENT_MAX_LENGTH = 50;

	// проверяем бот ли это
	public static function isBot():bool {

		$user_agent = $_SERVER["HTTP_USER_AGENT"] ?? "";

		foreach (self::_BOT_LIST as $bot) {

			if (stripos($user_agent, $bot) !== false) {
				return true;
			}
		}

		if (mb_strlen($user_agent) < self::_BOT_USER_AGENT_MAX_LENGTH) {
			return true;
		}

		return false;
	}
}