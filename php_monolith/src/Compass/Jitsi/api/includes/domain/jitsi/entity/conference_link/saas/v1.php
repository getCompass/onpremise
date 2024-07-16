<?php

namespace Compass\Jitsi;

/**
 * класс для работы с 1 версией ссылок на конференцию
 * @package Compass\Jitsi
 * @deprecated реализован чисто для поддержки прошлых версий ссылок
 */
class Domain_Jitsi_Entity_ConferenceLink_Saas_V1 implements Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler {

	/** @var string регулярка для парсинга параметров конференции из ссылки */
	protected const _LINK_REGEX = "/c\/([a-zA-Z0-9-]+)(?:\/)?\?pwd=([a-zA-Z0-9]+)/";

	/**
	 * парсим ссылку конференции и получаем параметры из которых она состоит
	 *
	 * @return Struct_Jitsi_Conference_ParsedLink
	 */
	public static function parse(string $link):Struct_Jitsi_Conference_ParsedLink {

		$result = preg_match(self::_LINK_REGEX, $link, $match_list);
		if (!$result || !isset($match_list[1], $match_list[2])) {
			throw new Domain_Jitsi_Exception_ConferenceLink_IncorrectLink();
		}

		return new Struct_Jitsi_Conference_ParsedLink(
			link: $link,
			conference_id: $match_list[1],
			password: $match_list[2],
			creator_user_id: 0,
		);
	}

	/**
	 * подготавливаем ссылку на лендинг страницу конференции
	 *
	 * @return string
	 */
	public static function prepareLandingConferenceLink(Struct_Db_JitsiData_Conference $conference):string {

		return sprintf("%s://%s/c/%s?pwd=%s", WEB_PROTOCOL_PUBLIC, DOMAIN_JITSI, $conference->conference_id, $conference->password);
	}

	/**
	 * подготавливаем ссылку на jitsi конференцию
	 *
	 * @return string
	 */
	public static function prepareJitsiConferenceLink(Struct_Db_JitsiData_Conference $conference,Struct_Jitsi_Node_Config $node_config, string $jwt_token):string {

		return sprintf("%s://%s/%s?jwt=%s", WEB_PROTOCOL_PUBLIC, $conference->jitsi_instance_domain, $conference->conference_id, $jwt_token);
	}

	/**
	 * подготавливаем ссылку на выдачу разрешения для медиа в конференции
	 *
	 * @return string
	 */
	public static function prepareJitsiRequestMediaPermissionsLink(Struct_Db_JitsiData_Conference $conference):string {

		return sprintf("%s://%s/requestMediaPermissions", WEB_PROTOCOL_PUBLIC, $conference->jitsi_instance_domain);
	}
}