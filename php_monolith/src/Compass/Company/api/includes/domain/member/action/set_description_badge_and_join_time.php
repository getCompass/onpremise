<?php

namespace Compass\Company;

/**
 * Действие обновления описания бейджа и даты вступления
 */
class Domain_Member_Action_SetDescriptionBadgeAndJoinTime {

	/**
	 *
	 * @param int          $user_id
	 * @param string|false $description
	 * @param int|false    $time
	 * @param int|false    $color_id
	 * @param string|false $content
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $user_id, string|false $description, int|false $time, int|false $color_id, string|false $content):void {

		// обновляем бейдж в транзакции
		Gateway_Db_CompanyData_Main::beginTransaction();

		$member_row = Gateway_Db_CompanyData_MemberList::getForUpdate($user_id);

		if ($color_id === false || $content === false) {
			$extra = \CompassApp\Domain\Member\Entity\Extra::doRemoveBadgeFromExtra($member_row->extra);
		} else {
			$extra = \CompassApp\Domain\Member\Entity\Extra::setBadgeInExtra($member_row->extra, $color_id, $content);
		}

		// формируем массив на обновление
		$updated = [
			"updated_at" => time(),
			"extra"      => $extra,
		];
		if ($description !== false) {
			$updated["short_description"] = $description;
		}
		if ($time !== false) {
			$updated["company_joined_at"] = $time;
		}
		Gateway_Db_CompanyData_MemberList::set($user_id, $updated);
		Gateway_Db_CompanyData_Main::commitTransaction();

		Gateway_Bus_CompanyCache::clearMemberCacheByUserId($user_id);

		// отправляем WS
		$user_info = Gateway_Db_CompanyData_MemberList::getOne($user_id);
		Gateway_Bus_Sender::memberProfileUpdated($user_info);

		// обновляем данные в intercom
		$badge = $content === false ? "" : $content;
		Gateway_Socket_Intercom::setMember($user_id, $badge, $description);
	}
}