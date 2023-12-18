<?php

namespace Compass\Pivot;

/**
 * Класс-репозиторий для работы с тарифом пространства.
 */
class Domain_SpaceTariff_Repository_Tariff {

	/**
	 * Загружает тариф из базы.
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function get(int $space_id):Domain_SpaceTariff_Tariff {

		// загружаем все записи для пространства
		// и формируем объект тарифа
		$row_list = Gateway_Db_PivotCompany_TariffPlan::getBySpace($space_id);
		return Domain_SpaceTariff_Tariff::load($row_list);
	}

	/**
	 * Выполняет обновление тарифного плана.
	 *
	 * @param int                                  $space_id
	 * @param \Tariff\Plan\MemberCount\MemberCount $plan
	 * @param array                                $payment_info
	 *
	 * @return \Tariff\Plan\MemberCount\MemberCount
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \queryException
	 */
	public static function updateMemberCount(int $space_id, \Tariff\Plan\MemberCount\MemberCount $plan, array $payment_info = []):array {

		// получаем данные для тарифа
		$data = $plan->getData();

		$insert_set = [
			"type"             => $data->plan_type,
			"plan_id"          => $data->plan_id,
			"active_till"      => $data->active_till,
			"free_active_till" => $data->free_active_till,
			"option_list"      => $data->option_list,
			"company_id"       => $space_id,
			"valid_till"       => MAX_TIMESTAMP_VALUE,
			"created_at"       => time(),
			"payment_info"     => $payment_info,
			"extra"            => [],
		];

		$tariff_plan_id         = Gateway_Db_PivotCompany_TariffPlan::insert($space_id, $insert_set);
		$tariff_plan_history_id = Gateway_Db_PivotCompany_TariffPlanHistory::insert($space_id, $insert_set);

		return [$plan, $tariff_plan_id, $tariff_plan_history_id];
	}
}
