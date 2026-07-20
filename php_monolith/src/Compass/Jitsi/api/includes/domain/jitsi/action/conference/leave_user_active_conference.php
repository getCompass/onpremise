<?php

namespace Compass\Jitsi;

/**
 * Завершить активную конференцию указанного пользователя.
 */
class Domain_Jitsi_Action_Conference_LeaveUserActiveConference {

	/**
	 * Выполняем действие
	 *
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \cs_CurlError
	 */
	public static function do(int $user_id, Struct_Db_JitsiData_UserActiveConference $user_active_conference):void {

		// получаем сущность конференции
		$conference = Domain_Jitsi_Entity_Conference::get($user_active_conference->active_conference_id);

		// исключаем участника из конференции
		try {

			Domain_Jitsi_Entity_Node_Request::init(Domain_Jitsi_Entity_Node::getConfig($conference->jitsi_instance_domain))->kickMember(
				$conference->conference_id,
				Domain_Jitsi_Entity_ConferenceMember_MemberId::prepareId(Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER, $user_id)
			);
		} catch (Domain_Jitsi_Exception_Node_RequestFailed $e) {

			// 404 возвращается в случае если конференция уже удалена, не сыпем логами просто так
			if ($e->getResponseHttpCode() !== 404) {

				// логируем ошибку и больше ничего не делаем
				$exception_message = \BaseFrame\Exception\ExceptionUtils::makeMessage($e, HTTP_CODE_500);
				\BaseFrame\Exception\ExceptionUtils::writeExceptionToLogs($e, $exception_message);
			}
		}

		// покидаем конференцию
		Domain_Jitsi_Scenario_Event::onConferenceMemberLeft(
			$user_active_conference->active_conference_id,
			Domain_Jitsi_Entity_ConferenceMember_MemberId::prepareId(Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER, $user_id)
		);
	}
}