<?php

namespace Compass\Company;

/**
 * Действие обновления full_name
 */
class Domain_Member_Action_SetPivotData {

	/**
	 * Экшен по обновлению данных пользователя
	 *
	 * @long
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function do(int $user_id, string $full_name, string $avatar_file_key, int $avatar_color_id, int $avg_screen_time, int $total_action_count,
					  int $avg_message_answer_time, int $profile_created_at, int $disabled_at, string $client_launch_uuid, int $is_deleted):void {

		// формируем массив на обновление
		$updated = [
			"updated_at"      => time(),
			"full_name"       => $full_name,
			"avatar_file_key" => $avatar_file_key,
		];

		Gateway_Db_CompanyData_MemberList::beginTransaction();
		$member = Gateway_Db_CompanyData_MemberList::getForUpdate($user_id);

		$member->extra = \CompassApp\Domain\Member\Entity\Extra::setIsDeleted($member->extra, $is_deleted);
		$member->extra = \CompassApp\Domain\Member\Entity\Extra::setAliasDisabledAt($member->extra, $disabled_at);
		$member->extra = \CompassApp\Domain\Member\Entity\Extra::setAliasAvgScreenTime($member->extra, $avg_screen_time);
		$member->extra = \CompassApp\Domain\Member\Entity\Extra::setAliasTotalActionCount($member->extra, $total_action_count);
		$member->extra = \CompassApp\Domain\Member\Entity\Extra::setAliasAvgMessageAnswerTime($member->extra, $avg_message_answer_time);
		$member->extra = \CompassApp\Domain\Member\Entity\Extra::setAvatarColorId($member->extra, $avatar_color_id);

		$member->avatar_file_key = $avatar_file_key;
		$updated["extra"]        = $member->extra;

		// если имя не такое же как и прошлое
		if ($member->full_name !== $full_name) {

			$member->full_name = $full_name;

			// прошло более 48 часов с момента регистрации пользователя в приложении - обновляем флаг смены имени
			if ((time() - $profile_created_at) > DAY2) {

				$updated["full_name_updated_at"] = time();
				$member->full_name_updated_at    = time();
			}
		}

		Gateway_Db_CompanyData_MemberList::set($user_id, $updated);
		Gateway_Db_CompanyData_MemberList::commitTransaction();

		// чистим кэш
		Gateway_Bus_CompanyCache::clearMemberCacheByUserId($user_id);

		// проводим действия после самой установки
		self::_afterSet($member, $full_name, $client_launch_uuid);
	}

	protected static function _afterSet(\CompassApp\Domain\Member\Struct\Main $member, string $full_name, string $client_launch_uuid):void {

		// отправляем WS
		Gateway_Bus_Sender::memberProfileUpdated($member, $client_launch_uuid);

		// диспатчим событие
		if ($full_name !== "") {

			$event = Type_Event_Member_NameChanged::create($member->user_id, $member->full_name);
			Gateway_Event_Dispatcher::dispatch($event, true);
		}
	}
}