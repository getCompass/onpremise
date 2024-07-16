<?php

namespace Compass\Jitsi;

/**
 * интерфейс описывающий класс, который в зависимости от окружения предоставляет класс-хендлер для работы с ссылками
 */
interface Domain_Jitsi_Entity_ConferenceLink_Interface_HandlerProvider {

	/**
	 * получаем текущий класс реализацию
	 *
	 * @return Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler
	 */
	public static function getCurrent():Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler;

	/**
	 * получаем класс в зависимости от переданной ссылки
	 *
	 * @return Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler
	 */
	public static function getByLink(string $link):Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler;

	/**
	 * получаем класс в зависимости от сущности конференции
	 *
	 * @return Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler
	 */
	public static function getByConference(Struct_Db_JitsiData_Conference $conference):Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler;
}