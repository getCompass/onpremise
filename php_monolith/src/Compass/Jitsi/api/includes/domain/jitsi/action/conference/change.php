<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Класс описывает действие по обновлению параметров постоянной конференции
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Action_Conference_Change {

	/**
	 * Выполняем действие
	 *
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws Domain_Jitsi_Exception_ConferenceMember_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_ConferenceIdDuplication
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceExist
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceIdDuplication
	 * @throws ParseFatalException
	 * @throws \cs_CurlError
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function do(int                            $user_id,
					  Struct_Db_JitsiData_Conference $conference,
					  string                         $conference_url_custom_name,
					  string                         $description):Struct_Api_Conference_Data {

		// обновляем параметры конференции
		$conference = Domain_Jitsi_Entity_PermanentConference::change($conference, $description);

		// если поменяли ссылку у постоянной конференции
		if ($conference_url_custom_name !== "" && $conference->conference_url_custom_name !== $conference_url_custom_name) {

			// проверяем что нет конференции от этого же пользователя с такой же ссылкой
			Domain_Jitsi_Entity_PermanentConference::assertLinkNotUsedByUser($user_id, $conference->space_id, $conference_url_custom_name);

			// завершаем постоянную комнату
			Domain_Jitsi_Action_Conference_FinishConference::do($user_id, $conference, true);

			// удаляем постоянную комнату из списка у пользователя
			Domain_Jitsi_Entity_PermanentConference::remove($conference->conference_id);

			// создаем с прошлым именем если нового не передали
			$description = $description === "" ? $conference->description : $description;

			// получаем инстанс доступного jitsi сервера, для создания конференции
			$jitsi_node_config = Domain_Jitsi_Entity_Node::getRandomNode();

			// создаем конференцию
			$conference = Domain_Jitsi_Action_Conference_CreatePermanent::do(
				$user_id, $conference->space_id, $conference->is_private, $conference->is_lobby, $jitsi_node_config, $conference_url_custom_name, $description);
		}

		return Struct_Api_Conference_Data::buildFromDB($conference);
	}
}