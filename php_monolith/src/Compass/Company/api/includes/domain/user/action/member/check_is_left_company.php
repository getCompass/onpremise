<?php

namespace Compass\Company;

/**
 * Action для проверки, уволен ли пользователь
 */
class Domain_User_Action_Member_CheckIsLeftCompany {

	// сколько максимально раз проверяется пользователь на факт покидания компании
	public const MAX_TRY_COUNT = 3;

	/**
	 * Проверка, уволен ли пользователь
	 *
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function do(int $member_id):bool {

		// проверяем, совпадает ли роль, которую передал клиент и ту, что хранится в базе
		$member = Domain_User_Action_Member_GetShort::do($member_id);

		try {
			$user_notification = Gateway_Db_CompanyData_MemberNotificationList::getOne($member_id);
		} catch (cs_UserNotificationNotFound) {

		}

		$rating_status        = Gateway_Bus_Company_Rating::getUserStatus($member_id);
		$member_session_cache = Gateway_Bus_CompanyCache::getSessionListByUserId($member_id);
		$member_session_db    = Gateway_Db_CompanyData_SessionActiveList::getByUser($member_id);
		$member_observer      = Type_User_Observer::getUserFromObserver($member_id);
		$count                = Gateway_Db_CompanyData_JoinLinkList::getCountAllByUserId($member_id, Domain_JoinLink_Entity_Main::STATUS_ACTIVE);
		$invite_link_list     = Gateway_Db_CompanyData_JoinLinkList::getAllByUserId($member_id, Domain_JoinLink_Entity_Main::STATUS_ACTIVE, $count);

		return $rating_status > Gateway_Bus_Company_Rating::USER_RATING_NOT_BLOCKED
			&& count($member_session_cache) < 1
			&& count($member_session_db) < 1
			&& count($member_observer) < 1
			&& count($invite_link_list) < 1
			&& $member->role == \CompassApp\Domain\Member\Entity\Member::ROLE_LEFT
			&& !isset($user_notification);
	}
}
