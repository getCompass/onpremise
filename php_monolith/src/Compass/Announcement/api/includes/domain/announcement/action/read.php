<?php declare(strict_types = 1);

namespace Compass\Announcement;

/**
 * Прочитать анонс
 */
class Domain_Announcement_Action_Read {

	/**
	 * Выполнить действие
	 *
	 * @param Struct_Db_AnnouncementMain_Announcement $announce
	 * @param int                                     $user_id
	 *
	 * @return void
	 */
	public static function do(Struct_Db_AnnouncementMain_Announcement $announce, int $user_id):void {

		// определяем время следующей переотправки
		$next_resend_at = $announce->resend_repeat_time > 0
			? time() + $announce->resend_repeat_time
			: 0;

		try {

			// получаем существующий анонс
			Gateway_Db_AnnouncementUser_UserAnnouncement::get($announce->announcement_id, $user_id);

			// обновляем статус и временные метки
			$set = [
				"is_read"             => 1,
				"next_resend_at"      => $next_resend_at,
				"resend_attempted_at" => 0,
				"updated_at"          => time(),
			];

			Gateway_Db_AnnouncementUser_UserAnnouncement::update($announce->announcement_id, $user_id, $set);
		} catch (\cs_RowIsEmpty) {

			// если связи не существует, то создаем
			Gateway_Db_AnnouncementUser_UserAnnouncement::insert($announce->announcement_id, $user_id, 1, $next_resend_at, []);
		}
	}
}
