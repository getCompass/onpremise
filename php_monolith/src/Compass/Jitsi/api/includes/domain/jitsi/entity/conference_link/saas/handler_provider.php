<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс, который определяет класс-реализацию для работы с ссылками на конференцию
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_ConferenceLink_Saas_HandlerProvider implements Domain_Jitsi_Entity_ConferenceLink_Interface_HandlerProvider {

	/**
	 * получаем текущий класс-реализацию
	 *
	 * @return Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler
	 */
	public static function getCurrent():Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler {

		return new Domain_Jitsi_Entity_ConferenceLink_Saas_V2();
	}

	/**
	 * определяем класс-реализацию по ссылке
	 *
	 * @return Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler
	 * @throws Domain_Jitsi_Exception_ConferenceLink_IncorrectLink
	 */
	public static function getByLink(string $link):Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler {

		// пробуем распарсить ссылку реализации v1 (поддержка старых ссылок)
		try {

			Domain_Jitsi_Entity_ConferenceLink_Saas_V1::parse($link);
			return new Domain_Jitsi_Entity_ConferenceLink_Saas_V1();
		} catch (Domain_Jitsi_Exception_ConferenceLink_IncorrectLink) {
			// не вышло, пробудем дальше
		}

		// пробуем распарсить ссылку реализации v2
		try {

			Domain_Jitsi_Entity_ConferenceLink_Saas_V2::parse($link);
			return new Domain_Jitsi_Entity_ConferenceLink_Saas_V2();
		} catch (Domain_Jitsi_Exception_ConferenceLink_IncorrectLink) {
			// не вышло
		}

		// не нашли подходящий класс – считаем что ссылка некорректная
		throw new Domain_Jitsi_Exception_ConferenceLink_IncorrectLink();
	}

	/**
	 * определяем класс-реализацию по сущности конференции
	 *
	 * @return Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler
	 */
	public static function getByConference(Struct_Db_JitsiData_Conference $conference):Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler {

		// если это реализация v1, то метод ниже вернет исключение
		try {
			Domain_Jitsi_Entity_Conference_Id::explodeConferenceId($conference->conference_id);
		} catch (ParseFatalException) {
			return new Domain_Jitsi_Entity_ConferenceLink_Saas_V1();
		}

		// во всех остальных случаях v2
		return new Domain_Jitsi_Entity_ConferenceLink_Saas_V2();
	}
}