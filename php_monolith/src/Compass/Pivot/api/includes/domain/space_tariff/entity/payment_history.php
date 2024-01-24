<?php

namespace Compass\Pivot;

/**
 * класс для работы с историей оплат тарифного плана
 * @package Compass\Pivot
 */
class Domain_SpaceTariff_Entity_PaymentHistory {

	/**
	 * Логируем оплату в истории
	 */
	public static function log(Struct_Db_PivotCompany_Company $space, array $payment_info, int $tariff_plan_history_id):void {

		// ID плательщика
		$customer_user_id = Domain_SpaceTariff_Entity_PaymentInfo::getCustomerUserID($payment_info);

		// ID платежа
		$payment_id = Domain_SpaceTariff_Entity_PaymentInfo::getPaymentID($payment_info);

		// создаем запись для пространства
		Gateway_Db_PivotCompany_TariffPlanPaymentHistory::insert(new Struct_Db_PivotCompany_TariffPlanPaymentHistory(
			null, $space->company_id, $customer_user_id, $tariff_plan_history_id, $payment_id, time(), time(), 0
		));

		// если плательщик – nobody, то ничего не логируем
		if ($customer_user_id < 1) {
			return;
		}

		// создаем запись для плательщика
		Gateway_Db_PivotUser_SpacePaymentHistory::insert(new Struct_Db_PivotUser_SpacePaymentHistory(
			null, $customer_user_id, $space->company_id, $tariff_plan_history_id, $payment_id, time(), time(), 0
		));
	}
}