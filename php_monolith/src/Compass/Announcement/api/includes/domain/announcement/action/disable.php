<?php declare(strict_types = 1);

namespace Compass\Announcement;

/**
 * Отключает анонсы по типу и указанным фильтрам.
 */
class Domain_Announcement_Action_Disable {

	/** @var int ограничение на кол-во прочитанных анонсов в запросе */
	protected const _PER_QUERY_LIMIT = 1000;

	/**
	 * Выполнить действие
	 *
	 * @param int   $type
	 * @param int   $company_id
	 * @param array $extra_filter_list
	 *
	 * @return void
	 */
	public static function do(int $type, int $company_id = 0,array $extra_filter_list = []):void {

		$allowed_status_list = Domain_Announcement_Entity::getActiveStatuses();
		$iteration           = 0;

		do {

			// считаем смещение
			$offset = static::_PER_QUERY_LIMIT * $iteration++;

			// читаем анонсы, подходящие для отключения
			$to_disable_list = Gateway_Db_AnnouncementMain_Announcement::getListToDisable(
				$type,
				$company_id,
				$allowed_status_list,
				static::_PER_QUERY_LIMIT,
				$offset
			);

			// проверяем анонсы и отключаем их по необходимости
			foreach ($to_disable_list as $announcement_to_disable) {
				static::_checkAndDisable($announcement_to_disable, $extra_filter_list);
			}

		} while (count($to_disable_list) >= static::_PER_QUERY_LIMIT);
	}

	/**
	 * Проверяет анонс и отключает его при необходимости.
	 *
	 * @param Struct_Db_AnnouncementMain_Announcement $announcement_to_disable
	 * @param array                                   $extra_filter_list
	 */
	protected static function _checkAndDisable(Struct_Db_AnnouncementMain_Announcement $announcement_to_disable, array $extra_filter_list):void {

		// если фильтр пустой, то удаляем
		if (count($extra_filter_list) === 0) {

			Gateway_Db_AnnouncementMain_Announcement::delete($announcement_to_disable->announcement_id);
			return;
		}

		// проверяем поля экстры
		foreach ($extra_filter_list as $filter_field => $filter_value) {

			$extra = $announcement_to_disable["extra"]["extra"];
			if (isset($extra[$filter_field]) && $extra[$filter_field] === $filter_value) {

				Gateway_Db_AnnouncementMain_Announcement::delete($announcement_to_disable->announcement_id);
				return;
			}
		}
	}
}
