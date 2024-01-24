<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_company_{10m}.tariff_plan_payment_history_{1}
 */
class Gateway_Db_PivotCompany_TariffPlanPaymentHistory extends Gateway_Db_PivotCompany_Main {

	protected const _TABLE_KEY = "tariff_plan_payment_history";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotCompany_TariffPlanPaymentHistory $payment_history):string {

		$shard_key  = self::_getDbKey($payment_history->space_id);
		$table_name = self::_getTableKey($payment_history->space_id);

		$insert = [
			"id"             => null,
			"space_id"       => $payment_history->space_id,
			"user_id"        => $payment_history->user_id,
			"tariff_plan_id" => $payment_history->tariff_plan_id,
			"payment_id"     => $payment_history->payment_id,
			"payment_at"     => $payment_history->payment_at,
			"created_at"     => $payment_history->created_at,
			"updated_at"     => $payment_history->updated_at,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * Получаем таблицу
	 *
	 */
	protected static function _getTableKey(int $space_id):string {

		return self::_TABLE_KEY . "_" . ceil($space_id / 1000000);
	}
}
