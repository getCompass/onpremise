<?php

namespace Compass\Company;

/**
 * Action обновления профиля пользователя
 */
class Domain_Member_Action_SetProfile {

	/**
	 * Выполняем
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function do(int $user_id, string|false $description, string|false $status, int|false $badge_color_id, string|false $badge_content):void {

		// обновляем бейдж в транзакции
		Gateway_Db_CompanyData_Main::beginTransaction();

		$member_row = Gateway_Db_CompanyData_MemberList::getForUpdate($user_id);

		if ($badge_color_id === 0 || $badge_content === "") {

			$extra = \CompassApp\Domain\Member\Entity\Extra::doRemoveBadgeFromExtra($member_row->extra);
		} else if ($badge_content === false || $badge_color_id === false) {

			// ничего не делаем
			$extra = $member_row->extra;
		} else {
			$extra = \CompassApp\Domain\Member\Entity\Extra::setBadgeInExtra($member_row->extra, $badge_color_id, $badge_content);
		}

		// формируем массив на обновление
		$updated                = [
			"updated_at" => time(),
			"extra"      => $extra,
		];
		$member_row->updated_at = $updated["updated_at"];
		$member_row->extra      = $updated["extra"];

		if ($description !== false) {

			$updated["short_description"]  = $description;
			$member_row->short_description = $updated["short_description"];
		}
		if ($status !== false) {

			$updated["comment"]  = $status;
			$member_row->comment = $updated["comment"];
		}
		Gateway_Db_CompanyData_MemberList::set($user_id, $updated);
		Gateway_Db_CompanyData_Main::commitTransaction();

		Gateway_Bus_CompanyCache::clearMemberCacheByUserId($user_id);

		// отправляем WS
		Gateway_Bus_Sender::memberProfileUpdated($member_row);

		// обновляем данные в intercom
		$badge = $badge_content === false ? "" : $badge_content;
		Gateway_Socket_Intercom::setMember($user_id, $badge, $description);
	}
}