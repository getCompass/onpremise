<?php

namespace Compass\Pivot;

/**
 * Базовый класс для получения отсортированного по order списка компаний пользователя
 */
class Domain_User_Action_GetOrderedCompanyList {

	/**
	 * Получаем список компаний
	 *
	 */
	public static function do(int $user_id, int $min_order, int $limit, bool $only_active):array {

		[$user_company_aggregates, $company_id_list_by_order] = self::_getUserCompanyListByOrder($user_id, $min_order, $limit, $only_active);

		$company_list = Gateway_Db_PivotCompany_CompanyList::getList(array_values($company_id_list_by_order));

		$output_company_list = [];
		foreach ($company_list as $company) {

			foreach ($user_company_aggregates as $order => $data) {

				if ($company_id_list_by_order[$order] == $company->company_id) {
					$output_company_list[] = Struct_User_Company::createFromCompanyStruct($company, $data["status"], $order, $data["inviter_user_id"]);
				}
			}
		}

		usort($output_company_list, function(Struct_User_Company $a, Struct_User_Company $b) {

			return $b->order <=> $a->order;
		});

		$output_company_list = array_slice($output_company_list, 0, $limit);

		if (count($output_company_list) < $limit) {
			$min_order = 0;
		} else {

			$last      = end($output_company_list);
			$min_order = $last->order == 1 ? 0 : $last->order;
		}
		return [$output_company_list, $min_order];
	}

	// получаем список компаний пользователя
	protected static function _getUserCompanyListByOrder(int $user_id, int $min_order, int $limit, bool $only_active):array {

		$user_company_lobby_list = [];
		Gateway_Db_PivotUser_Main::beginTransaction($user_id);

		$user_company_list = Gateway_Db_PivotUser_CompanyList::getCompanyListWithMinOrder($user_id, $min_order, $limit + 1);
		if (!$only_active) {
			$user_company_lobby_list = Gateway_Db_PivotUser_CompanyLobbyList::getCompanyListWithMinOrder($user_id, $min_order, $limit + 1);
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
