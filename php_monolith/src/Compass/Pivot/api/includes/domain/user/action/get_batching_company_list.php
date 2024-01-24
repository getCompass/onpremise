<?php

namespace Compass\Pivot;

/**
 * Базовый класс для получения списка компаний по массиву id
 */
class Domain_User_Action_GetBatchingCompanyList {

	/**
	 * Получаем список компаний
	 */
	public static function do(int $user_id, array $company_id_list, bool $only_active):array {

		[$user_company_aggregates, $company_id_list_by_order] = self::_getUserCompanyListByOrder($user_id, $company_id_list, $only_active);
		$company_list = Gateway_Db_PivotCompany_CompanyList::getList(array_values($company_id_list_by_order));

		$output_company_list = [];
		foreach ($company_list as $company) {

			foreach ($user_company_aggregates as $order => $data) {

				if ($company_id_list_by_order[$order] == $company->company_id) {
					$output_company_list[] = Struct_User_Company::createFromCompanyStruct($company, $data["status"], $order, $data["inviter_user_id"]);
				}
			}
		}

		return $output_company_list;
	}

	/**
	 * Получаем список компаний пользователя
	 *
	 * @throws \returnException
	 */
	protected static function _getUserCompanyListByOrder(int $user_id, array $company_id_list, bool $only_active):array {

		$user_company_lobby_list = [];
		Gateway_Db_PivotUser_Main::beginTransaction($user_id);

		$user_company_list = Gateway_Db_PivotUser_CompanyList::getCompanyListById($user_id, $company_id_list);
		if (!$only_active) {
			$user_company_lobby_list = Gateway_Db_PivotUser_CompanyLobbyList::getCompanyListById($user_id, $company_id_list);
		}

		Gateway_Db_PivotUser_Main::commitTransaction($user_id);

		$user_company_aggregates  = [];
		$company_id_list_by_order = [];
		foreach ($user_company_list as $user_company) {

			$user_company_aggregates[$user_company->order]  = [
				"status"          => Struct_User_Company::ACTIVE_STATUS,
				"inviter_user_id" => 0,
			];
			$company_id_list_by_order[$user_company->order] = $user_company->company_id;
		}

		foreach ($user_company_lobby_list as $user_company) {

			$user_company_aggregates[$user_company->order]  = [
				"status"          => Domain_Company_Entity_User_Lobby::TEXT_STATUS_SCHEMA[$user_company->status],
				"inviter_user_id" => Domain_Company_Entity_User_Lobby::getInviterUserId($user_company->extra),
			];
			$company_id_list_by_order[$user_company->order] = $user_company->company_id;
		}

		return [$user_company_aggregates, $company_id_list_by_order];
	}
}
