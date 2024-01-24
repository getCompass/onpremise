<?php declare(strict_types = 1);

namespace Compass\Announcement;

/**
 * Отключает анонс по его идентификатору.
 * Такое отключение свойственно только для вспомогательного функционала.
 */
class Domain_Announcement_Action_DisableById {

	/**
	 * Выполнить действие
	 *
	 * @param int $announcement_id
	 *
	 * @return void
	 */
	public static function do(int $announcement_id):void {

		Gateway_Db_AnnouncementMain_Announcement::delete($announcement_id);
	}
}
