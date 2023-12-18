<?php

namespace Compass\Company;

/**
 * Action для проверки, состоит ли пользователь в компании
 */
class Domain_User_Action_Member_CheckIsInCompany {

	/**
	 * Проверка, уволен ли пользователь
	 *
	 * @param int $member_id
	 *
	 * @return bool
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \apiAccessException
	 */
	public static function do(int $member_id):bool {

		$member = Domain_User_Action_Member_GetShort::do($member_id);

		$rating_status   = Gateway_Bus_Company_Rating::getUserStatus($member_id);
		$member_observer = Type_User_Observer::getUserFromObserver($member_id);

		return $rating_status != Gateway_Bus_Company_Rating::USER_RATING_NOT_BLOCKED
			&& count($member_observer) > 0
			&& $member->role != \CompassApp\Domain\Member\Entity\Member::ROLE_LEFT;
	}
}
