<?php

namespace Compass\Jitsi;

/**
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Action_Conference_RecreateJitsiConference {

	/**
	 * Пересоздаем комнату в Jitsi
	 *
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws \cs_CurlError
	 */
	public static function do(Struct_Db_JitsiData_Conference $conference):void {

		// пароль для зала ожидания
		// в бизнес логике этого нет, но нельзя оставлять конференцию с включенным лобби без установленного пароля
		$lobby_password = "";
		if ($conference->is_lobby) {
			$lobby_password = generateRandomString(64);
		}

		// создаем конференцию в jitsi
		try {
			Domain_Jitsi_Entity_Node_Request::init(Domain_Jitsi_Entity_Node::getConfig($conference->jitsi_instance_domain))
				->createRoom($conference->conference_id, $conference->is_lobby, $lobby_password);
		} catch (Domain_Jitsi_Exception_Node_RequestFailed $e) {

			// если такая комната уже существует, то ничего не делаем
			if ($e->getResponseHttpCode() == 409) {
				return;
			}

			throw $e;
		}
	}
}