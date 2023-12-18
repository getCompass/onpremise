<?php

namespace Compass\Company;

/**
 * Класс для получения списка ссылок активных ссылок по типу
 */
class Domain_JoinLink_Action_GetActiveListByType {

	/**
	 * Метод для получения массива записей
	 *
	 * @return Struct_Db_CompanyData_JoinLink[]
	 */
	public static function do(array $type_list):array {

		$count = Gateway_Db_CompanyData_JoinLinkList::getCountByTypeAndStatus($type_list, Domain_JoinLink_Entity_Main::STATUS_ACTIVE, time());
		return Gateway_Db_CompanyData_JoinLinkList::getByTypeAndStatus($type_list, Domain_JoinLink_Entity_Main::STATUS_ACTIVE, time(), $count);
	}
}
