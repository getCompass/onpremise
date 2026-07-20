<?php

namespace Compass\Jitsi;

/**
 * Пометить все конференции пользователя удаленными при кике
 */
class Domain_Jitsi_Action_Conference_RemoveWhenUserKick
{
	/**
	 * Выполняем действие
	 *
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \cs_CurlError
	 * @throws \parseException
	 * @long
	 */
	public static function do(int $user_id, int $space_id): void
	{

		// получаем все постоянные конференции созданные пользователем
		$permanent_conference_list = Domain_Jitsi_Entity_PermanentConference::getList($user_id, $space_id);

		// помечаем все комнаты удаленными
		Gateway_Db_JitsiData_PermanentConferenceList::setBySpace($user_id, $space_id, [
			"is_deleted" => 1,
			"updated_at" => time(),
		]);

		$permanent_conference_id_list = [];
		foreach ($permanent_conference_list as $conference) {
			$permanent_conference_id_list[] = $conference->conference_id;
		}

		// пытаемся получить активную конференцию
		$user_active_conference = null;
		try {

			$user_active_conference = Domain_Jitsi_Entity_UserActiveConference::get($user_id);
			$conference             = Domain_Jitsi_Entity_Conference::get($user_active_conference->active_conference_id);

			// если она есть и являемся ее создателем, добавляем ее в массив на удаление
			if ($conference->creator_user_id === $user_id) {
				$permanent_conference_id_list[] = $conference->conference_id;
			}
		} catch (Domain_Jitsi_Exception_UserActiveConference_NotFound | Domain_Jitsi_Exception_Conference_NotFound) {
			// ничего не делаем
		}

		$conference_list = Domain_Jitsi_Entity_Conference::getList($permanent_conference_id_list);
		foreach ($conference_list as $conference) {

			try {
				Domain_Jitsi_Action_Conference_FinishConference::do($user_id, $conference, true);
			} catch (Domain_Jitsi_Exception_Conference_NotFound | Domain_Jitsi_Exception_ConferenceMember_NotFound
			| Domain_Jitsi_Exception_Node_RequestFailed | Domain_Jitsi_Exception_Node_NotFound) {

				// игнорируем чтобы не ломать увольнение пользователя, конференцию закончить и самостоятельно можно
			}
		}

		try {
			// исключаем пользователя из активной конференции
			if (!is_null($user_active_conference) && !in_array($user_active_conference->active_conference_id, $permanent_conference_id_list)) {
				Domain_Jitsi_Action_Conference_LeaveUserActiveConference::do($user_id, $user_active_conference);
			}
		} catch (Domain_Jitsi_Exception_Conference_NotFound | Domain_Jitsi_Exception_ConferenceMember_NotFound | Domain_Jitsi_Exception_Node_NotFound) {
			// игнорируем
		}

	}
}
