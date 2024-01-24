<?php

namespace Compass\Pivot;

/**
 * Класс для работы с отправкой алертов по пространствам тарифов
 */
class Domain_SpaceTariff_Entity_Alert {

	/**
	 * Выполняет отправку сообщения.
	 */
	public static function send(string $text):void {

		// проверяем, что параметры корректные
		if (COMPASS_NOTICE_BOT_TOKEN_NEW === "" || COMPASS_NOTICE_BOT_SIGNATURE_KEY === "" || PLAN_TARIFF_CRON_OBSERVE_COMPASS_NOTICE_GROUP_ID === "") {
			return;
		}

		Type_Notice_Compass::sendGroupNew(
			COMPASS_NOTICE_BOT_TOKEN_NEW,
			COMPASS_NOTICE_BOT_SIGNATURE_KEY,
			PLAN_TARIFF_CRON_OBSERVE_COMPASS_NOTICE_GROUP_ID,
			$text
		);
	}
}