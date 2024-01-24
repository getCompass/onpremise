<?php

namespace Compass\Company;

/**
 * Класс для получения списка ссылок активных ссылок
 */
class Domain_JoinLink_Action_GetActiveList {

	/**
	 * Метод для получения массива записей
	 *
	 * @return Struct_Db_CompanyData_JoinLink[]
	 */
	public static function do():array {

		$count = Gateway_Db_CompanyData_JoinLinkList::getCountActiveList(Domain_JoinLink_Entity_Main::STATUS_ACTIVE, time());
		return Gateway_Db_CompanyData_JoinLinkList::getActiveList(Domain_JoinLink_Entity_Main::STATUS_ACTIVE, time(), $count);
	}
}
