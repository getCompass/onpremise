<?php

namespace Compass\Jitsi;

/**
 * Завершить конференцию
 */
class Domain_Jitsi_Action_Conference_FinishConference {

	/**
	 * Выполняем действие
	 *
	 * @param int                            $user_id
	 * @param Struct_Db_JitsiData_Conference $conference
	 *
	 * @return void
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws Domain_Jitsi_Exception_ConferenceMember_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \cs_CurlError
	 * @throws \parseException
	 * @long
	 */
	public static function do(int $user_id, Struct_Db_JitsiData_Conference $conference, bool $is_finish_conference = false):void {

		$conference_id = $conference->conference_id;

		// отправляем запрос на завершение конференции
		try {
			Domain_Jitsi_Entity_Node_Request::init(Domain_Jitsi_Entity_Node::getConfig($conference->jitsi_instance_domain))->destroyRoom($conference_id);
		} catch (Domain_Jitsi_Exception_Node_RequestFailed $e) {

			// в таком случае выполним событие завершения конференции, чтобы она не повисла активной для оставшихся участников
			Domain_Jitsi_Scenario_Event::onConferenceFinished($conference_id);

			// смоделируем, что пользователь завершающий конференцию – покинул ее
			Domain_Jitsi_Scenario_Event::onConferenceMemberLeft(
				$conference_id,
				Domain_Jitsi_Entity_ConferenceMember_MemberId::prepareId(Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER, $user_id)
			);

			// если вернулась 404, то значит комната уже удалена
			if ($e->getResponseHttpCode() == 404) {
				return;
			}

			// иначе падаем
			throw $e;
		}

		// при finishConference создатель сингл-конфы должен получить вс о завершении конференции
		// конфа завершается асинхронно
		if (!$is_finish_conference || Domain_Jitsi_Entity_Conference_Data::getConferenceType($conference->data) != Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_SINGLE) {
			return;
		}

		try {
			$conference = Domain_Jitsi_Entity_Conference::get($conference_id);
		} catch (Domain_Jitsi_Exception_Conference_NotFound) {
			return;
		}

		// отправляем событие для создателя конференции
		$conference->status = Domain_Jitsi_Entity_Conference::STATUS_FINISHED;
		Gateway_Bus_SenderBalancer::activeConferenceUpdated(
			[$conference->creator_user_id],
			Gateway_Bus_SenderBalancer_Event_ActiveConferenceUpdated_V1::ACTION_UPDATED_CONFERENCE_DATA,
			Struct_Api_Conference_Data::buildFromDB($conference),
			null,
			null,
			null,
		);
	}
}