<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Класс для работы с отправкой алертов
 */
class Domain_System_Entity_Alert {

	/**
	 * Выполняет отправку сообщения.
	 */
	public static function sendBusinessStat(string $text):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		// проверяем, что параметры корректные
		if (BUSINESS_COMPASS_NOTICE_BOT_TOKEN === "" || BUSINESS_COMPASS_NOTICE_BOT_SIGNATURE_KEY === "" || BUSINESS_STAT_COMPASS_NOTICE_GROUP_ID === "") {
			return;
		}

		Type_Notice_Compass::sendGroupNew(
			BUSINESS_COMPASS_NOTICE_BOT_TOKEN,
			BUSINESS_COMPASS_NOTICE_BOT_SIGNATURE_KEY,
			BUSINESS_STAT_COMPASS_NOTICE_GROUP_ID,
			$text
		);
	}
}