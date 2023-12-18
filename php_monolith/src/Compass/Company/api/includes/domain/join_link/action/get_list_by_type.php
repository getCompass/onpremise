<?php

namespace Compass\Company;

/**
 * Класс для получения списка ссылок по типу
 */
class Domain_JoinLink_Action_GetListByType {

	/**
	 * Метод для получения массива записей
	 *
	 * @return Struct_Db_CompanyData_JoinLink[]
	 */
	public static function do(int $limit):array {

		return Gateway_Db_CompanyData_JoinLinkList::getList([
			Domain_JoinLink_Entity_Main::STATUS_ACTIVE,
			Domain_JoinLink_Entity_Main::STATUS_USED,
		], $limit);
	}
}
