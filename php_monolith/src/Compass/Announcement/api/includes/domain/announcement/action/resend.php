<?php declare(strict_types = 1);

namespace Compass\Announcement;

/**
 * Переотправить анонс
 */
class Domain_Announcement_Action_Resend {

	protected const _PER_ITERATION_LIMIT = 500;

	/**
	 * Выполнить действие
	 *
	 * @param Struct_Db_AnnouncementMain_Announcement $announcement
	 * @param array                                   $receiver_list
	 *
	 * @return void
	 */
	public static function do(Struct_Db_AnnouncementMain_Announcement $announcement, array $receiver_list = []):void {

		if (count($receiver_list) > 0) {

			static::_resendToList($announcement, $receiver_list);
			return;
		}

		if (count($announcement->receiver_user_id_list) > 0) {

			static::_resendToList($announcement, $announcement->receiver_user_id_list);
			return;
		}

		static::_resendToAll($announcement);
	}

	/**
	 * Форсированно отправляет анонс указанному списку получателей.
	 *
	 * @param Struct_Db_AnnouncementMain_Announcement $announcement
	 * @param array                                   $receiver_list
	 */
	protected static function _resendToList(Struct_Db_AnnouncementMain_Announcement $announcement, array $receiver_list):void {

		// определяем время следующей переотправки
		$next_resend_at = time();

		// обновляем данные для пересылки анонса пользователям
		Gateway_Db_AnnouncementUser_UserAnnouncement::updateNextResendAttemptedAt($announcement->announcement_id, $receiver_list, 0, $next_resend_at);
		Gateway_Bus_SenderBalancer::announcementPublished($receiver_list);
	}

	/**
	 * Форсированно ставим анонс на прочтение всем пользователям, которые в теории имеют к нему доступ.
	 *
	 * @param Struct_Db_AnnouncementMain_Announcement $announcement
	 */
	protected static function _resendToAll(Struct_Db_AnnouncementMain_Announcement $announcement):void {

		// определяем время следующей переотправки
		$next_resend_at = time();

		// для каждой таблицы перебираем пользователей
		foreach (Gateway_Db_AnnouncementUser_UserAnnouncement::getAllTableShards() as $table_name) {

			$iteration = 0;

			do {

				// получаем существующий анонс
				$receiver_list = Gateway_Db_AnnouncementUser_UserAnnouncement::getAnnouncementResendReceivers(
					$table_name,
					$announcement->announcement_id,
					$next_resend_at,
					static::_PER_ITERATION_LIMIT,
					$iteration++ * static::_PER_ITERATION_LIMIT,
				);

				// убираем тех, кто не должен получить анонс
				// такое возможно при обновлении анонса, когда ранее к нему имел доступ тот, кто теперь не имеет
				$receiver_list = array_diff($receiver_list, $announcement->excluded_user_id_list);

				// обновляем данные для пересылки анонса пользователям
				Gateway_Db_AnnouncementUser_UserAnnouncement::updateNextResendAttemptedAt($announcement->announcement_id, $receiver_list, 0, $next_resend_at);
				Gateway_Bus_SenderBalancer::announcementPublished($receiver_list);
			} while (count($receiver_list) >= static::_PER_ITERATION_LIMIT);
		}
	}
}
