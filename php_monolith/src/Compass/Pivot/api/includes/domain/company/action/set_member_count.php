<?php

namespace Compass\Pivot;

/**
 * Action для установки количества участников компании
 */
class Domain_Company_Action_SetMemberCount {

	/**
	 * Устанавливаем количество участников компании
	 *
	 * @param Struct_Db_PivotCompany_Company $company
	 * @param int|false                      $member_count
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(Struct_Db_PivotCompany_Company $company, int|false $member_count, int|false $guest_count):void {

		assertTestServer();

		//получаем количество участников компании
		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		if ($member_count !== false) {

			$extra = Domain_Company_Entity_Company::setMemberCount($company->extra, $member_count);
			Gateway_Db_PivotCompany_CompanyList::set($company->company_id, ["extra" => $extra]);
		} else {
			$member_count = Domain_Company_Entity_Company::getMemberCount($company->extra);
		}

		if ($guest_count !== false) {

			$extra = Domain_Company_Entity_Company::setGuestCount($company->extra, $guest_count);
			Gateway_Db_PivotCompany_CompanyList::set($company->company_id, ["extra" => $extra]);
		} else {
			$guest_count = Domain_Company_Entity_Company::getGuestCount($company->extra);
		}

		Gateway_Socket_Company::setMemberCount($company->company_id, $member_count, $guest_count, $company->domino_id, $private_key);
	}
}
