<?php

namespace Compass\Jitsi;

/**
 * интерфейс описывающий класс для работы с ссылкой
 */
interface Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler {

	/**
	 * парсим ссылку конференции и получаем параметры из которых она состоит
	 *
	 * @return Struct_Jitsi_Conference_ParsedLink
	 */
	public static function parse(string $link):Struct_Jitsi_Conference_ParsedLink;

	/**
	 * подготавливаем ссылку на лендинг страницу конференции
	 *
	 * @return string
	 */
	public static function prepareLandingConferenceLink(Struct_Db_JitsiData_Conference $conference):string;

	/**
	 * подготавливаем ссылку на jitsi конференцию
	 *
	 * @return string
	 */
	public static function prepareJitsiConferenceLink(Struct_Db_JitsiData_Conference $conference, Struct_Jitsi_Node_Config $node_config, string $jwt_token):string;

	/**
	 * подготавливаем ссылку на выдачу разрешения для медиа в конференции
	 *
	 * @return string
	 */
	public static function prepareJitsiRequestMediaPermissionsLink(Struct_Db_JitsiData_Conference $conference):string;
}