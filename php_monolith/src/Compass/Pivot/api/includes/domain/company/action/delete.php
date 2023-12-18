<?php

namespace Compass\Pivot;

/**
 * Базовый класс для действия удаления компании собственником
 */
class Domain_Company_Action_Delete {

	/**
	 * Удалить компанию
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_CompanyUserIsNotOwner
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $user_id, Struct_Db_PivotCompany_Company $company):Struct_Db_PivotCompany_Company {

		// если компанию уже удалили
		if ($company->is_deleted) {
			return $company;
		}

		$deleted_at  = time();
		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		// отправляем сокет запрос в компанию на удаление
		Gateway_Socket_Company::delete($user_id, $deleted_at, $company->company_id, $company->domino_id, $private_key);

		// удаляем компанию на пивоте
		$company->is_deleted = 1;
		$company->deleted_at = $deleted_at;
		$company->status     = Domain_Company_Entity_Company::COMPANY_STATUS_DELETED;

		Gateway_Db_PivotCompany_CompanyList::set($company->company_id, [
			"is_deleted" => $company->is_deleted,
			"deleted_at" => $company->deleted_at,
			"status"     => $company->status,
		]);

		// удаляем компаиню из обзерва
		Gateway_Db_PivotCompany_CompanyTierObserve::delete($company->company_id);

		// отправляем таск на перемещение всех пользователей в предбанник
		Type_Phphooker_Main::onCompanyDelete($user_id, $company->company_id);

		// отмечаем в intercom, что пространство было удалено
		Gateway_Socket_Intercom::spaceDeleted($company->company_id);

		return $company;
	}
}