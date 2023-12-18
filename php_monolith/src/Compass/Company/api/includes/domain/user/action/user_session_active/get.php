<?php

namespace Compass\Company;

/**
 * Action для получения активной сессии юзера
 */
class Domain_User_Action_UserSessionActive_Get {

	/**
	 * Получить активную сессию юзера
	 *
	 * @param string $session_uniq Уникальный ключ сессии
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function do(string $session_uniq):Struct_Db_CompanyData_SessionActive {

		return Gateway_Db_CompanyData_SessionActiveList::getOne($session_uniq);
	}
}
