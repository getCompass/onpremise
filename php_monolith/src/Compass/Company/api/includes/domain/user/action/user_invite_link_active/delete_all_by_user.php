<?php

namespace Compass\Company;

/**
 * Action для удаления всех активных ссылок-инвайтов пользователя
 */
class Domain_User_Action_UserInviteLinkActive_DeleteAllByUser {

	/**
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $user_id):void {

		// помечаем все инвайты-ссылки от пользователя недействительными
		$limit            = Gateway_Db_CompanyData_JoinLinkList::getCountAllByUserId($user_id, Domain_JoinLink_Entity_Main::STATUS_ACTIVE);
		$invite_link_list = Gateway_Db_CompanyData_JoinLinkList::getAllByUserId($user_id, Domain_JoinLink_Entity_Main::STATUS_ACTIVE, $limit);

		foreach ($invite_link_list as $invite_link) {
			Domain_JoinLink_Action_Delete::do($invite_link, $user_id);
		}
	}
}
