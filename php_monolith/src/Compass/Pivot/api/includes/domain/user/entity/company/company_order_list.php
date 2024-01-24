<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * класс для представления company_order запроса
 */
class Domain_User_Entity_Company_CompanyOrderList {

	/**
	 * Валидируем список company_order
	 *
	 * @param array $company_order_list
	 *
	 * @return void
	 * @throws cs_DuplicateCompanyId
	 * @throws cs_DuplicateOrder
	 * @throws cs_MissedValue
	 * @throws cs_WrongValue
	 */
	public static function assertValidInputData(array $company_order_list):void {

		$order_list  = [];
		$company_ids = [];

		// валидируем входные данные и собираем id компаний
		foreach ($company_order_list as $key => $company_order) {

			// проверяем не пропустил ли клиент нужные поля
			if (!isset($company_order["order"]) || !isset($company_order["company_id"])) {
				throw new ParamException("order or company_id field not found");
			}

			if ($company_order["company_id"] < 1) {
				throw new cs_WrongValue("Invalid company_id in list");
			}

			$order      = (int) $company_order["order"];
			$company_id = (int) $company_order["company_id"];

			// проверяем, не передал ли клиент, повторяющиеся позиции
			if (in_array($order, $order_list)) {
				throw new cs_DuplicateOrder("Duplicate order");
			}

			// проверяем, не передал ли клиент, повторяющиеся id компаний
			if (in_array($company_id, $company_ids)) {
				throw new cs_DuplicateCompanyId("Duplicate company_id");
			}

			$order_list[]  = $order;
			$company_ids[] = $company_id;
		}
	}

	/**
	 * Преобразовать массив класса-структуры Struct_Db_PivotUser_UserCompany в массив company_order
	 *
	 */
	public static function userCompanyListToCompanyOrderList(array $company_list):array {

		return array_map(function(Struct_Db_PivotUser_Company $company) {

			return [
				"company_id" => $company->company_id,
				"order"      => $company->order,
			];
		}, $company_list);
	}
}
