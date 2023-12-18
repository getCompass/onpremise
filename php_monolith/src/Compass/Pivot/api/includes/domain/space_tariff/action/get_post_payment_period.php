<?php

namespace Compass\Pivot;

/**
 * Вспомогательный класс для получения постоплатного периода
 */
class Domain_SpaceTariff_Action_GetPostPaymentPeriod {

	/**
	 * Выполняем
	 * @return int
	 */
	public static function do():int {

		return getConfig("TARIFF")["member_count"]["postpayment_period"];
	}
}