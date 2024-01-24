<?php

namespace Compass\Announcement;

/**
 * Класс для работы с получателя анонса.
 */
class Domain_Announcement_Entity_Receiver {

	/**
	 * Проверяет, является ли пользователь получателем.
	 *
	 * @param int                                     $user_id
	 * @param Struct_Db_AnnouncementMain_Announcement $announcement
	 *
	 * @return bool
	 */
	public static function isUserReceiver(int $user_id, Struct_Db_AnnouncementMain_Announcement $announcement):bool {

		// определяем является ли пользователь получателем
		$receiver_list = static::resolveList($announcement);

		// если получателей нет
		if ($receiver_list === false) {
			return false;
		}

		// если получатели есть, но пользователь в них не указан
		if (count($receiver_list) > 0 && !in_array($user_id, $receiver_list)) {
			return false;
		}

		// в отличие от общего метода, получение по пользователю проверит,
		// не попадает ли пользователь в список исключений для анонса
		if ($announcement->is_global && count($announcement->excluded_user_id_list) > 0 && in_array($user_id, $announcement->excluded_user_id_list)) {
			return false;
		}

		return true;
	}

	/**
	 * Возвращает список id пользователей, которым нужно доставить анонс.
	 *
	 * Есть три варианта развития событий:
	 * — массив с id пользователей — шлем только им
	 * — пустой массив — шлем всем
	 * — булево false — известных получателей нет
	 *
	 * @param Struct_Db_AnnouncementMain_Announcement $announcement
	 *
	 * @return int[]|false
	 */
	public static function resolveList(Struct_Db_AnnouncementMain_Announcement $announcement):array|false {

		return $announcement->is_global
			? static::_resolveGlobal($announcement)
			: static::_resolveCompany($announcement);
	}

	/**
	 * Определяет получателей для глобальных анонсов.
	 *
	 * @param Struct_Db_AnnouncementMain_Announcement $announcement
	 *
	 * @return int[]|false
	 */
	protected static function _resolveGlobal(Struct_Db_AnnouncementMain_Announcement $announcement):array|false {

		// если список получателей задан явно, то отправляем только им
		if (count($announcement->receiver_user_id_list) > 0) {
			return arrayValuesInt($announcement->receiver_user_id_list);
		}

		// даже если там явно указан список тех, кому анонс не нужно доставлять
		// считаем, что они входят в список получателей для глобальных

		return [];
	}

	/**
	 * Определяет получателей для компанейских анонсов.
	 *
	 * @param Struct_Db_AnnouncementMain_Announcement $announcement
	 *
	 * @return int[]|false
	 */
	protected static function _resolveCompany(Struct_Db_AnnouncementMain_Announcement $announcement):array|false {

		// если вдруг по какой-то причине ид компании не указан
		if ($announcement->company_id === 0) {
			return false;
		}

		// получаем всех известных участников компании
		$company_user_id_list = Gateway_Db_AnnouncementCompany_CompanyUser::getAllUsersByCompany($announcement->company_id);

		if (count($announcement->receiver_user_id_list) > 0) {

			// если участники заданы явно, отдаем только тех, про кого знаем
			$company_user_id_list = arrayValuesInt(array_intersect($company_user_id_list, $announcement->receiver_user_id_list));
		}

		if (count($announcement->excluded_user_id_list) > 0) {

			// если если список игнорируемых, то выкидываем их из списка получателей
			$company_user_id_list = arrayValuesInt(array_diff($company_user_id_list, $announcement->excluded_user_id_list));
		}

		// возвращаем оставшихся, если никого нет, то возвращаем false
		return count($company_user_id_list) ? arrayValuesInt($company_user_id_list) : false;
	}
}
