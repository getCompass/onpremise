<?php

namespace Compass\Jitsi;

/**
 * класс для работы со 2 версией ссылок на конференцию
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_ConferenceLink_Saas_V2 implements Domain_Jitsi_Entity_ConferenceLink_Interface_LinkHandler {

	/** @var int длинна пароля для 2 версии */
	protected const _PASSWORD_LENGTH = 8;

	/** @var string регулярка для парсинга параметров конференции из ссылки */
	protected const _LINK_REGEX = "/([a-zA-Z0-9-]+)(?:\/)?\?pwd=([a-zA-Z0-9]+)/";

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

		// извлекаем из параметра пароля user_id создателя и непосредственно пароль
		$password_parameter = $match_list[2];
		$creator_user_id    = mb_substr($password_parameter, 0, -self::_PASSWORD_LENGTH);
		if (mb_strlen($creator_user_id) < 1 || !is_numeric($creator_user_id) || $creator_user_id < 1) {
			throw new Domain_Jitsi_Exception_ConferenceLink_IncorrectLink();
		}
		$password = mb_substr($password_parameter, mb_strlen($creator_user_id));
		if (mb_strlen($password) !== self::_PASSWORD_LENGTH) {
			throw new Domain_Jitsi_Exception_ConferenceLink_IncorrectLink();
		}

		// формируем conference_id
		$conference_id = Domain_Jitsi_Entity_Conference_Id::getConferenceId(intval($creator_user_id), $match_list[1], $password);

		return new Struct_Jitsi_Conference_ParsedLink(
			link: $link,
			conference_id: $conference_id,
			password: $password,
			creator_user_id: $creator_user_id,
		);
	}

	/**
	 * подготавливаем ссылку на лендинг страницу конференции
	 *
	 * @return string
	 */
	public static function prepareLandingConferenceLink(Struct_Db_JitsiData_Conference $conference):string {

		// парсим conference_id
		[$creator_user_id, $unique_part, $password] = Domain_Jitsi_Entity_Conference_Id::explodeConferenceId($conference->conference_id);

		return sprintf("%s://%s/%s?pwd=%d%s", WEB_PROTOCOL_PUBLIC, DOMAIN_JITSI, $unique_part, $conference->creator_user_id, $conference->password);
	}

	/**
	 * подготавливаем ссылку на jitsi конференцию
	 *
	 * @return string
	 */
	public static function prepareJitsiConferenceLink(Struct_Db_JitsiData_Conference $conference, Struct_Jitsi_Node_Config $node_config, string $jwt_token):string {

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