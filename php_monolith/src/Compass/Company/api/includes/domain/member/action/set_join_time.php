<?php

namespace Compass\Company;

/**
 * Действие обновления время присоединения к компании
 */
class Domain_Member_Action_SetJoinTime {

	/**
	 *
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function do(int $user_id, int $time):void {

		// формируем массив на обновление
		$updated = [
			"updated_at"        => time(),
			"company_joined_at" => $time,
		];

		Gateway_Db_CompanyData_MemberList::set($user_id, $updated);
		Gateway_Bus_CompanyCache::clearMemberCacheByUserId($user_id);

		$user_info = Gateway_Db_CompanyData_MemberList::getOne($user_id);

		// отправляем WS
		Gateway_Bus_Sender::memberProfileUpdated($user_info);

		// обновляем время в обсервере
		self::_updateObserver($user_id);
	}

	/**
	 * Обновить время в обсервере на установку времени поздравления с годовщиной
	 *
	 * @param int $user_id
	 *
	 * @return void
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 */
	protected static function _updateObserver(int $user_id):void {

		$user_observer      = Type_User_Observer::getUserFromObserver($user_id);
		$user_observer_data = $user_observer["data"];

		$job_extra      = Type_User_Observer_Notify_EmployeeAnniversary::provideJobExtra($user_id, Type_User_Observer::JOB_TYPE_NOTIFY_ABOUT_EMPLOYEE_ANNIVERSARY);
		$next_work_time = Type_User_Observer_Notify_EmployeeAnniversary::getNextWorkTime($job_extra);

		$user_observer_data[Type_User_Observer::JOB_TYPE_NOTIFY_ABOUT_EMPLOYEE_ANNIVERSARY]["need_work"] = $next_work_time;
		Type_User_Observer::updateUserFromObserver($user_id, ["data" => $user_observer_data]);
	}
}