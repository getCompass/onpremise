<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_ConferenceLink_Main {

	/**
	 * в зависимости от окружения возвращаем класс, который определит класс-обработчик для работы с ссылками
	 *
	 * @return Domain_Jitsi_Entity_ConferenceLink_Interface_HandlerProvider
	 * @throws ParseFatalException
	 */
	public static function getHandlerProvider():Domain_Jitsi_Entity_ConferenceLink_Interface_HandlerProvider {

		if (ServerProvider::isSaas()) {
			return new Domain_Jitsi_Entity_ConferenceLink_Saas_HandlerProvider();
		}

		if (ServerProvider::isOnPremise()) {
			return new Domain_Jitsi_Entity_ConferenceLink_OnPremise_HandlerProvider();
		}

		throw new ParseFatalException("unexpected environment");
	}
}