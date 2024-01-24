<?php

namespace Compass\Pivot;

/**
 * Уведомить о блокировке пространства
 */
class Domain_Space_Action_Tariff_BlockNotify {

	/**
	 * Выполняем действие
	 *
	 * @param Struct_Db_PivotCompany_Company $company
	 * @param \BaseFrame\System\Log          $log
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(Struct_Db_PivotCompany_Company $company, \BaseFrame\System\Log $log):array {

		$tariff = Domain_SpaceTariff_Repository_Tariff::get($company->company_id);

		// если пространство активно - завершаем выполнение
		if ($tariff->memberCount()->isActive(time())) {
			return [true, $log];
		}

		$private_key    = Domain_Company_Entity_Company::getPrivateKey($company->extra);
		$user_role_list = Gateway_Socket_Company::getUserRoleList(
			[Domain_Company_Entity_User_Member::ROLE_ADMINISTRATOR], $company->company_id, $company->domino_id, $private_key);
		$owner_user_id  = $user_role_list[Domain_Company_Entity_User_Member::ROLE_ADMINISTRATOR][0];

		// отправляем в интерком сообщение о том, что пространство заблокировано
		Gateway_Socket_Intercom::onSpaceTariffBlocked($owner_user_id, $company->company_id);

		$log = $log->addText("Оповестили о блокировке пространства");

		return [true, $log];
	}
}