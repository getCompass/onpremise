<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс описывающий действие по созданию конференции
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Action_Conference_Create {

	/**
	 * Выполняем действие
	 *
	 * @throws Domain_Jitsi_Exception_Conference_ConferenceIdDuplication
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws ParseFatalException
	 * @throws \cs_CurlError
	 * @throws \queryException
	 */
	public static function do(int $user_id, int $space_id, bool $is_private, bool $is_lobby, Struct_Jitsi_Node_Config $jitsi_node_config, ?string $custom_unique_part):Struct_Db_JitsiData_Conference {

		// формируем пароль
		$password = Domain_Jitsi_Entity_Conference::generatePassword();

		// формируем ID конференции
		$unique_part = is_null($custom_unique_part) ? Domain_Jitsi_Entity_Conference_Id::generateRandomUniquePart() : $custom_unique_part;

		// создаем сущность конференции
		$conference_draft                = Domain_Jitsi_Entity_Conference::makeDraft($user_id, $space_id, $jitsi_node_config->domain, "", "");
		$conference_draft->conference_id = Domain_Jitsi_Entity_Conference_Id::getConferenceId($user_id, $unique_part, $password);
		$conference_draft->password      = $password;
		$conference_draft->is_private    = $is_private;
		$conference_draft->is_lobby      = $is_lobby;
		$conference                      = Domain_Jitsi_Entity_Conference::create($conference_draft);

		// пароль для зала ожидания
		// в бизнес логике этого нет, но нельзя оставлять конференцию с включенным лобби без установленного пароля
		$lobby_password = "";
		if ($is_lobby) {
			$lobby_password = generateRandomString(64);
		}

		// создаем конференцию в jitsi
		Domain_Jitsi_Entity_Node_Request::init(Domain_Jitsi_Entity_Node::getConfig($conference->jitsi_instance_domain))
			->createRoom($conference->conference_id, $is_lobby, $lobby_password);

		return $conference;
	}
}