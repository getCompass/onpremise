<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс описывает действие по обновлению параметров конференции
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Action_Conference_SetOptions {

	/**
	 * Выполняем действие
	 *
	 * @throws \cs_CurlError
	 * @throws \parseException
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws ParseFatalException
	 */
	public static function do(Struct_Db_JitsiData_Conference $conference, bool $is_private, bool $is_lobby):Struct_Db_JitsiData_Conference {

		// обновляем параметры конференции
		$conference = Domain_Jitsi_Entity_Conference::setOptions($conference, $is_private, $is_lobby);

		// переключаем зал ожидания в комнате
		$jitsi_request = Domain_Jitsi_Entity_Node_Request::init(Domain_Jitsi_Entity_Node::getConfig($conference->jitsi_instance_domain));
		try {
			$is_lobby ? $jitsi_request->enableRoomLobby($conference->conference_id, generateRandomString(64)) : $jitsi_request->disableRoomLobby($conference->conference_id);
		} catch (Domain_Jitsi_Exception_Node_RequestFailed $e) {

			// если это не 404 (комната не найдена), то что-то серьезное
			// 404 может вернуться в случае, если конференция завершается и со стороны jitsi сущность была уже удалена, но не успела
			// синхронизироваться с приложением
			if ($e->getResponseHttpCode() != 404) {
				throw $e;
			}
		}

		// обновленные данные о конференции
		$conference_data = Struct_Api_Conference_Data::buildFromDB($conference);

		// получаем всех модераторов конференции
		$conference_member_moderator_list = Domain_Jitsi_Entity_ConferenceMember::getConferenceModeratorList($conference->conference_id);
		$conference_member_moderator_list = Domain_Jitsi_Entity_ConferenceMember::filterByMemberType($conference_member_moderator_list, Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER);
		$moderator_user_id_list           = array_map(
			static fn(Struct_Db_JitsiData_ConferenceMember $conference_moderator) => intval(Domain_Jitsi_Entity_ConferenceMember_MemberId::resolveId($conference_moderator->member_id)),
			$conference_member_moderator_list
		);

		// отправляем ws-событие
		Gateway_Bus_SenderBalancer::conferenceOptionsUpdated($moderator_user_id_list, $conference_data);

		return $conference;
	}
}