<?php

namespace Compass\Pivot;

/**
 * Действие проверки, что пользователь в пространстве может изменять настройки компании
 */
class Domain_User_Action_CheckCanEditSpaceSettings {

	/**
	 * проверяем
	 *
	 * @param int                            $user_id
	 * @param Struct_Db_PivotCompany_Company $company
	 *
	 * @throws Domain_User_Exception_UserCantEditSpaceSettings
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $user_id, Struct_Db_PivotCompany_Company $company):void {

		// проверяем, можем ли мы менять настройки компании
		$private_key             = Domain_Company_Entity_Company::getPrivateKey($company->extra);
		$can_edit_space_settings = Gateway_Socket_Company::checkCanEditSpaceSettings($user_id, $company->company_id, $company->domino_id, $private_key);
		if ($can_edit_space_settings === false) {
			throw new Domain_User_Exception_UserCantEditSpaceSettings("user cant edit space settings");
		}
	}
}