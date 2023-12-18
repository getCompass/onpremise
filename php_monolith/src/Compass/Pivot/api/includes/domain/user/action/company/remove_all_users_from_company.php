<?php

namespace Compass\Pivot;

/**
 * Убрать компанию из списка активных у всех пользователей компании
 *
 */
class Domain_User_Action_Company_RemoveAllUsersFromCompany {

	/**
	 * Установить компанию неактивной у всех участников компании
	 *
	 */
	public static function do(int $deleted_by_user_id, int $company_id):void {

		$user_id_list = Gateway_Db_PivotCompany_CompanyUserList::getFullUserIdList($company_id);

		// для добавления в предбанник берем всех, кроме инициатора - у него компания не должна появится в списке после удаления
		$user_company_list = Gateway_Db_PivotUser_CompanyList::getListForCompany(array_diff($user_id_list, [$deleted_by_user_id]), $company_id);

		// добавляем всех юзеров в предбанник
		Domain_Company_Entity_User_Lobby::addDeletedCompanyUserList($user_company_list);

		// удаляем всех пользователей из компании
		Gateway_Db_PivotCompany_CompanyUserList::deleteByCompany($company_id);
		Gateway_Db_PivotUser_CompanyList::deleteByCompany($company_id);

		// отправляем эвент собственнику об удалении компании для других активных устройств
		Gateway_Bus_SenderBalancer::companyDeleted([$deleted_by_user_id], $company_id, time());
	}
}
