<?php

namespace Compass\Pivot;

/**
 * Класс для взаимодействия с порядком в списке компаний
 */
class Domain_Company_Entity_User_Order {

	/**
	 * Изменяем порядок
	 *
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_DuplicateOrder
	 */
	public static function set(int $user_id, array $companies_order):void {

		$time                        = time();
		$order_list                  = [];
		$need_update_company_id_list = [];

		foreach ($companies_order as $company_order) {

			$order_list[$company_order["company_id"]] = $company_order["order"];
			$need_update_company_id_list[]            = $company_order["company_id"];
		}

		// получаем все компании пользователя (в том числе из лобби)
		$all_active_company_list = Gateway_Db_PivotUser_CompanyList::getCompanyList($user_id);
		$all_lobby_company_list  = Gateway_Db_PivotUser_CompanyLobbyList::getCompanyListWithMinOrder($user_id, 0, 100);

		// достаём компании, которые нужно будет обновить
		[$active_company_list, $lobby_company_list] = self::_getCompaniesForNeedUpdate($all_active_company_list, $all_lobby_company_list, $order_list, $need_update_company_id_list);

		Gateway_Db_PivotUser_Main::beginTransaction($user_id);

		// обновляем активные компании
		foreach ($active_company_list as $active_company) {

			Gateway_Db_PivotUser_CompanyList::set($user_id, $active_company->company_id, [
				"order"      => $order_list[$active_company->company_id],
				"updated_at" => $time,
			]);
		}

		// обновляем компании в лобби
		foreach ($lobby_company_list as $lobby_company) {

			Gateway_Db_PivotUser_CompanyLobbyList::set($user_id, $lobby_company->company_id, [
				"order"      => $order_list[$lobby_company->company_id],
				"updated_at" => $time,
			]);
		}
		Gateway_Db_PivotUser_Main::commitTransaction($user_id);
	}

	/**
	 * получаем компании, которые нужно будет обновить
	 *
	 * @throws cs_DuplicateOrder
	 */
	protected static function _getCompaniesForNeedUpdate(array $all_active_company_list, array $all_lobby_company_list, array $order_list, array $need_update_company_id_list):array {

		$active_company_list = [];
		$lobby_company_list  = [];

		/** @var Struct_Db_PivotUser_Company $company */
		foreach ($all_active_company_list as $company) {

			// проверяем, если попалась компания, которая имеет тот же order, но она отсутствует в списке на обновление
			// значит появятся продублированные order, поэтому ругаемся
			if (in_array($company->order, $order_list) && !isset($order_list[$company->company_id])) {
				throw new cs_DuplicateOrder("Duplicate order");
			}

			// если активная компания находится в списке на обновление, то берём её
			if (in_array($company->company_id, $need_update_company_id_list)) {
				$active_company_list[] = $company;
			}
		}

		foreach ($all_lobby_company_list as $lobby_company) {

			// проверяем, если попалась компания, которая имеет тот же order, но она отсутствует в списке на обновление
			// значит появятся продублированные order, поэтому ругаемся
			if (in_array($lobby_company->order, $order_list) && !isset($order_list[$lobby_company->company_id])) {
				throw new cs_DuplicateOrder("Duplicate order");
			}

			// если компания из лобби находится в списке на обновление, то берём её
			if (in_array($lobby_company->company_id, $need_update_company_id_list)) {
				$lobby_company_list[] = $lobby_company;
			}
		}

		return [$active_company_list, $lobby_company_list];
	}

	/**
	 * Изменяем порядок
	 *
	 */
	public static function getMaxOrder(int $user_id):int {

		$user_order       = Gateway_Db_PivotUser_CompanyList::getMaxOrder($user_id);
		$user_lobby_order = Gateway_Db_PivotUser_CompanyLobbyList::getMaxOrder($user_id);

		return max($user_order, $user_lobby_order);
	}
}