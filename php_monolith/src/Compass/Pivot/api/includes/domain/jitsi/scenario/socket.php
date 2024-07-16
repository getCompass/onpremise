<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\CompanyNotServedException;

/**
 * Сценарии для сокетов Jitsi
 */
class Domain_Jitsi_Scenario_Socket {

	/**
	 * Проверяет что указанный пользователь может начать
	 * видеоконференцию в указанном пространстве.
	 *
	 * @param int $user_id
	 * @param int $opponent_user_id
	 * @param int $space_id
	 *
	 * @return string
	 * @throws CompanyNotServedException
	 * @throws Domain_Jitsi_Exception_GuestIsInitiator
	 * @throws Domain_Jitsi_Exception_IsNotAllowed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_CompanyIsHibernate
	 */
	public static function checkIsAllowedForCall(int $user_id, int $opponent_user_id, int $space_id):string {

		try {

			$company_row = Domain_Company_Entity_Company::get($space_id);
		} catch (cs_CompanyNotExist|cs_CompanyIncorrectCompanyId) {
			throw new Domain_Company_Exception_NotExist("company not exist");
		}

		try {
			return Gateway_Socket_Conversation::checkIsAllowedForCall($company_row, $user_id, $opponent_user_id);
		} catch (Gateway_Socket_Exception_Conversation_InitiatorIsGuest) {
			throw new Domain_Jitsi_Exception_GuestIsInitiator("guest is initiator");
		} catch (Gateway_Socket_Exception_Conversation_ActionIsNotAllowed) {
			throw new Domain_Jitsi_Exception_IsNotAllowed("action is not allowed");
		}
	}
}