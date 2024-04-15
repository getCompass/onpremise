<?php

namespace Application\System\ExceptionSender;

use Application\System\Notice;

/**
 * устарелый провайдер для отправки исключений
 * @package Application\System\ExceptionSender
 */
class Legacy implements ProviderInterface {

	public const PROVIDER = "legacy";

	protected static ?Notice $_instance = null;

	/** отправляем сообщение с исключением */
	public static function send(string $text):void {

		// если не заполнены константы, то ничего не делаем
		if (!defined("LEGACY_NOTICE_PROVIDER_ENDPOINT") || LEGACY_NOTICE_PROVIDER_ENDPOINT == "" ||
			!defined("LEGACY_NOTICE_PROVIDER_BOT_USER_ID") || LEGACY_NOTICE_PROVIDER_BOT_USER_ID == "" ||
			!defined("LEGACY_NOTICE_PROVIDER_BOT_TOKEN") || LEGACY_NOTICE_PROVIDER_BOT_TOKEN == "" ||
			!defined("LEGACY_NOTICE_PROVIDER_CHANNEL_KEY") || LEGACY_NOTICE_PROVIDER_CHANNEL_KEY == "") {
			return;
		}

		$client = self::_instance();
		$client->sendGroup(LEGACY_NOTICE_PROVIDER_CHANNEL_KEY, $text);
	}

	/** singleton */
	protected static function _instance():Notice {

		if (!is_null(static::$_instance)) {
			return static::$_instance;
		}

		static::$_instance = new Notice(LEGACY_NOTICE_PROVIDER_ENDPOINT, LEGACY_NOTICE_PROVIDER_BOT_USER_ID, LEGACY_NOTICE_PROVIDER_BOT_TOKEN);
		return static::$_instance;
	}
}