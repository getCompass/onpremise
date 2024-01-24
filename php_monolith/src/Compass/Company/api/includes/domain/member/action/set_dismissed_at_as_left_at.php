<?php

namespace Compass\Company;

/**
 * Действие обновления времени увольнения у пользователей
 */
class Domain_Member_Action_SetDismissedAtAsLeftAt {

	/**
	 * выполняем
	 *
	 * @param \CompassApp\Domain\Member\Struct\Main[] $member_list
	 *
	 * @throws \parseException
	 * @throws \busException
	 */
	public static function do(array $member_list):void {

		foreach ($member_list as $member) {

			$user_id = $member->user_id;

			// если ранее уже проходились по пользователю
			if ($member->left_at > 0) {
				continue;
			}

			// получаем время увольнения из компании
			$left_at = \CompassApp\Domain\Member\Entity\Extra::getDismissedAt($member->extra);

			// убираем время увольнения из extra
			unset($member->extra["extra"]["dismissed_at"]);

			$set = [
				"left_at" => $left_at,
				"extra"   => $member->extra,
			];
			Gateway_Db_CompanyData_MemberList::set($user_id, $set);
		}
	}
}