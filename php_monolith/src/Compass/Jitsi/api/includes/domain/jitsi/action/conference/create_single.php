<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Создать сингл звонок
 */
class Domain_Jitsi_Action_Conference_CreateSingle {

	/**
	 * выполняем действие
	 *
	 * @param int                      $creator_user_id
	 * @param int                      $opponent_user_id
	 *
	 * @param int                      $space_id
	 * @param Struct_Jitsi_Node_Config $jitsi_node_config
	 *
	 * @return Struct_Db_JitsiData_Conference
	 * @throws Domain_Jitsi_Exception_Conference_ConferenceIdDuplication
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws ParseFatalException
	 * @throws \cs_CurlError
	 * @throws \queryException
	 */
	public static function do(int $creator_user_id, int $opponent_user_id, int $space_id, string $conversation_map, Struct_Jitsi_Node_Config $jitsi_node_config):Struct_Db_JitsiData_Conference {

		// создаем сущность конференции
		$conference_draft             = Domain_Jitsi_Entity_Conference::makeDraft($creator_user_id, $space_id, $jitsi_node_config->domain);
		$conference_draft->is_private = true;
		$conference_draft->is_lobby   = false;
		$conference_draft->data       = Domain_Jitsi_Entity_Conference_Data::setConferenceType(
			$conference_draft->data, Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_SINGLE);
		$conference_draft->data       = Domain_Jitsi_Entity_Conference_Data::setOpponentUserId($conference_draft->data, $opponent_user_id);
		$conference_draft->data       = Domain_Jitsi_Entity_Conference_Data::setConversationMap($conference_draft->data, $conversation_map);
		$conference                   = Domain_Jitsi_Entity_Conference::create($conference_draft);

		// создаем конференцию в jitsi
		Domain_Jitsi_Entity_Node_Request::init(Domain_Jitsi_Entity_Node::getConfig($conference->jitsi_instance_domain))
			->createRoom($conference->conference_id, $conference_draft->is_lobby, "");

		return $conference;
	}
}