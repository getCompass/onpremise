<?php

namespace Compass\Pivot;

/**
 * Action для получения количества участников компании
 */
class Domain_Company_Action_GetMemberCount {

	/**
	 * Получаем количество участников компании
	 *
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 */
	public static function do(Struct_Db_PivotCompany_Company $company):int {

		// получаем количество участников компании
		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);
		return Gateway_Socket_Company::getMemberCount($company->company_id, $company->domino_id, $private_key);
	}
}
