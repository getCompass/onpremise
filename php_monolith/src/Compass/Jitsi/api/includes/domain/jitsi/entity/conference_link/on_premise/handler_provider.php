<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * класс, который определяет класс-реализацию для работы с ссылками на конференцию
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_ConferenceLink_OnPremise_HandlerProvider implements Domain_Jitsi_Entity_ConferenceLink_Interface_HandlerProvider {

	/**
	 * получаем текущий класс-реализацию
	 *
	 * @return Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler
	 */
	public static function getCurrent():Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler {

		return new Domain_Jitsi_Entity_ConferenceLink_OnPremise_V1();
	}

	/**
	 * определяем класс-реализацию по ссылке
	 *
	 * @param string $link
	 *
	 * @return Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler
	 */
	public static function getByLink(string $link):Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler {

		return new Domain_Jitsi_Entity_ConferenceLink_OnPremise_V1();
	}

	/**
	 * определяем класс-реализацию по сущности конференции
	 *
	 * @return Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler
	 */
	public static function getByConference(Struct_Db_JitsiData_Conference $conference):Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler {

		return new Domain_Jitsi_Entity_ConferenceLink_OnPremise_V1();
	}
}