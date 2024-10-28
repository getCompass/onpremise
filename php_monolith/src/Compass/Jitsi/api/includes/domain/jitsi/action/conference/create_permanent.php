<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс описывающий действие по созданию постоянной конференции
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Action_Conference_CreatePermanent {

	/**
	 * Выполняем действие
	 *
	 * @throws Domain_Jitsi_Exception_Conference_ConferenceIdDuplication
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceIdDuplication
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function do(int $user_id, int $space_id, bool $is_private, bool $is_lobby, Struct_Jitsi_Node_Config $jitsi_node_config, string $conference_url_custom_name, string $description):Struct_Db_JitsiData_Conference {

		// формируем пароль
		$password = Domain_Jitsi_Entity_Conference::generatePassword();

		// создаем сущность конференции
		$conference_draft                = Domain_Jitsi_Entity_Conference::makeDraft($user_id, $space_id, $jitsi_node_config->domain, $conference_url_custom_name, $description);
		$conference_draft->conference_id = Domain_Jitsi_Entity_Conference_Id::getConferenceId($user_id, $conference_url_custom_name, $password);
		$conference_draft->password      = $password;
		$conference_draft->is_private    = $is_private;
		$conference_draft->is_lobby      = $is_lobby;
		$conference_draft->data          = Domain_Jitsi_Entity_Conference_Data::setConferenceType($conference_draft->data, Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_PERMANENT);
		$conference                      = Domain_Jitsi_Entity_Conference::create($conference_draft);

		Domain_Jitsi_Entity_PermanentConference::create($conference_draft);

		return $conference;
	}
}