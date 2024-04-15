<?php

namespace Application\System\ExceptionSender;

use GetCompass\Userbot\Bot;

/**
 * провайдер для отправки исключений в Compass через userbot api
 * @package Application\System\ExceptionSender
 */
class Compass implements ProviderInterface {

	public const PROVIDER = "compass";

	protected static ?Bot $_instance = null;

	/** отправляем сообщение с исключением */
	public static function send(string $text):void {

		// если не заполнены константы, то ничего не делаем
		if (!defined("COMPASS_NOTICE_PROVIDER_ENDPOINT") || COMPASS_NOTICE_PROVIDER_ENDPOINT == "" ||
			!defined("COMPASS_NOTICE_PROVIDER_BOT_TOKEN") || COMPASS_NOTICE_PROVIDER_BOT_TOKEN == "" ||
			!defined("COMPASS_NOTICE_PROVIDER_CHAT_ID") || COMPASS_NOTICE_PROVIDER_CHAT_ID == "") {

			console("exit");
			return;
		}

		console("send");
		$client = self::_instance();
		$client->sendGroupMessage(COMPASS_NOTICE_PROVIDER_CHAT_ID, $text);
	}

	/** singleton */
	protected static function _instance():Bot {

		if (!is_null(static::$_instance)) {
			return static::$_instance;
		}

		!defined("GET_COMPASS_CURL_URL") && define("GET_COMPASS_CURL_URL", COMPASS_NOTICE_PROVIDER_ENDPOINT);
		static::$_instance = new Bot(COMPASS_NOTICE_PROVIDER_BOT_TOKEN);
		return static::$_instance;
	}
}