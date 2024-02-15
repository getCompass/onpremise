<?php

namespace Compass\Pivot;

use Tariff\Plan\MemberCount\MemberCount;

/**
 * класс для формирования события об оплате в пространстве
 */
class Domain_Partner_Entity_Event_SpacePayment extends Domain_Partner_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "partner.space_payment";

	/**
	 * Создаем событие
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $space_id, string $payment_id, MemberCount $member_count, int $tariff_prolongation_duration):void {

		$params = [
			"space_id"                     => $space_id,
			"payment_id"                   => $payment_id,
			"tariff_member_limit"          => $member_count->getLimit(),
			"tariff_prolongation_duration" => $tariff_prolongation_duration,
			"tariff_active_till"           => $member_count->getActiveTill(),
			"tariff_is_trial"              => $member_count->isTrial(time()),
			"tariff_is_paid"               => $member_count->isActive(time()) && !$member_count->isFree(time()),
		];

		// отправляем в партнерское ядро
		static::_sendToPartner($params);
	}

}