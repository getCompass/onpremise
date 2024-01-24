<?php declare(strict_types = 1);

namespace Compass\Announcement;

/**
 * Сохраняет и публикует анонс
 */
class Domain_Announcement_Action_Publish {

	/**
	 * Выполнить действие.
	 *
	 * @param Struct_Db_AnnouncementMain_Announcement $announcement
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement
	 *
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(Struct_Db_AnnouncementMain_Announcement $announcement):Struct_Db_AnnouncementMain_Announcement {

		// вставляем или обновляем анонс
		$announcement = static::_publishOrUpdate($announcement);
		static::_sendWsEvent($announcement);

		return $announcement;
	}

	/**
	 * Публикует или обновляет анонс.
	 *
	 * @param Struct_Db_AnnouncementMain_Announcement $published_announcement
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement
	 *
	 * @throws \queryException
	 * @throws \returnException
	 *
	 * @long длинные структуры
	 */
	protected static function _publishOrUpdate(Struct_Db_AnnouncementMain_Announcement $published_announcement):Struct_Db_AnnouncementMain_Announcement {

		// если анонс не является уникальным, то просто публикуем его как новый и не задумываемся ни о чем
		if (!Domain_Announcement_Entity::isUniqueType($published_announcement->type)) {
			return static::_publish($published_announcement);
		}

		/** начинаем транзакцию */
		Gateway_Db_AnnouncementMain_Announcement::beginTransaction();

		try {

			// получаем существующий
			$existing_announcement = Gateway_Db_AnnouncementMain_Announcement::getExistingForUpdate($published_announcement->type, $published_announcement->company_id, Domain_Announcement_Entity::getActiveStatuses());
		} catch (\cs_RowIsEmpty) {

			Gateway_Db_AnnouncementMain_Announcement::rollback();
			return static::_publish($published_announcement);
		}

		// делаем обновленный анонс
		$updated_announcement = static::_makeUpdatedAnnouncement($published_announcement, $existing_announcement);

		// выполняем вставку анонса
		Gateway_Db_AnnouncementMain_Announcement::update(
			$updated_announcement->announcement_id,
			$updated_announcement->priority,
			$updated_announcement->expires_at,
			$updated_announcement->receiver_user_id_list,
			$updated_announcement->excluded_user_id_list,
			$updated_announcement->extra
		);

		Gateway_Db_AnnouncementMain_Announcement::commitTransaction();
		/** заканчиваем транзакцию */

		return $updated_announcement;
	}

	/**
	 * Публикует анонс.
	 *
	 * @param Struct_Db_AnnouncementMain_Announcement $announcement
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement
	 * @throws \queryException
	 */
	protected static function _publish(Struct_Db_AnnouncementMain_Announcement $announcement):Struct_Db_AnnouncementMain_Announcement {

		return Gateway_Db_AnnouncementMain_Announcement::insert(
			(int) $announcement->is_global,
			$announcement->type,
			$announcement->status,
			$announcement->company_id,
			$announcement->priority,
			$announcement->expires_at,
			$announcement->resend_repeat_time,
			$announcement->receiver_user_id_list,
			$announcement->excluded_user_id_list,
			$announcement->extra,
		);
	}

	/**
	 * Возвращает обновленный анонс.
	 *
	 * @param Struct_Db_AnnouncementMain_Announcement $published_announcement
	 * @param Struct_Db_AnnouncementMain_Announcement $existing_announcement
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement
	 */
	protected static function _makeUpdatedAnnouncement(Struct_Db_AnnouncementMain_Announcement $published_announcement, Struct_Db_AnnouncementMain_Announcement $existing_announcement):Struct_Db_AnnouncementMain_Announcement {

		return new Struct_Db_AnnouncementMain_Announcement(
			$existing_announcement->announcement_id,
			$existing_announcement->is_global,
			$existing_announcement->type,
			$existing_announcement->status,
			$existing_announcement->company_id,
			$published_announcement->priority,
			$existing_announcement->created_at,
			time(),
			$published_announcement->expires_at,
			$existing_announcement->resend_repeat_time,
			$published_announcement->receiver_user_id_list,
			$published_announcement->excluded_user_id_list,
			$published_announcement->extra,
		);
	}

	/**
	 * Отправляет ws событие тем, кто должен быть уведомлен об анонсе.
	 *
	 * @param Struct_Db_AnnouncementMain_Announcement $announcement
	 */
	protected static function _sendWsEvent(Struct_Db_AnnouncementMain_Announcement $announcement):void {

		// получаем список тех, кому нужно доставить уведомление о наличии нового анонса
		$receiver_list = Domain_Announcement_Entity_Receiver::resolveList($announcement);

		// получателей не нашлось
		if ($receiver_list === false) {
			return;
		}

		if (count($receiver_list) === 0) {

			// есть конкретные получатели
			Gateway_Bus_SenderBalancer::globalAnnouncementPublished();
		} else {

			// шлем анонс вообще всем
			Gateway_Bus_SenderBalancer::announcementPublished($receiver_list);
		}
	}
}
