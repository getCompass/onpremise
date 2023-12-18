<?php

namespace Compass\Company;

/**
 * Класс для получения списка ссылок неактивных ссылок
 */
class Domain_JoinLink_Action_GetInactiveList {

	protected const _INACTIVE_EXPIRES_LAST_TIME = DAY1 * 30; // дата для учёты ссылок за последние N дней

	/**
	 * Метод для получения массива записей
	 *
	 * @return Struct_Db_CompanyData_JoinLink[]
	 */
	public static function do(int $limit, int $offset, bool $is_need_last_time = false):array {

		// дата, до какого актуальны неактивные ссылки
		// всё что ранее - фильтруем
		$last_time_at = 0;
		if ($is_need_last_time) {
			$last_time_at = time() - self::_INACTIVE_EXPIRES_LAST_TIME;
		}

		return Gateway_Db_CompanyData_JoinLinkList::getInactiveList(
			Domain_JoinLink_Entity_Main::STATUS_ACTIVE, Domain_JoinLink_Entity_Main::STATUS_USED, time(), $last_time_at, $limit, $offset
		);
	}
}
