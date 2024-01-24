<?php

namespace Compass\Company;

/**
 * Класс для получения информации о инвайте-ссылке
 */
class Domain_JoinLink_Action_Get {

	/**
	 * Выполняем получение ссылки
	 *
	 * @throws cs_JoinLinkNotExist
	 */
	public static function do(string $join_link_uniq):Struct_Db_CompanyData_JoinLink {

		try {
			return Gateway_Db_CompanyData_JoinLinkList::getOne($join_link_uniq);
		} catch (\cs_RowIsEmpty) {
			throw new cs_JoinLinkNotExist();
		}
	}
}
