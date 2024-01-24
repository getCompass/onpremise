<?php

namespace Compass\Company;

/**
 * Action для получения участника компании
 */
class Domain_User_Action_Member_GetShort {

	/**
	 * Получить участника компании
	 *
	 * @param int $user_id
	 *
	 * @return \CompassApp\Domain\Member\Struct\Short
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \cs_RowIsEmpty
	 */
	public static function do(int $user_id):\CompassApp\Domain\Member\Struct\Short {

		// получаем информацию о пользователе
		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id]);
		if (!isset($user_info_list[$user_id])) {
			throw new \cs_RowIsEmpty("not found in company_cache");
		}
		return $user_info_list[$user_id];
	}
}
