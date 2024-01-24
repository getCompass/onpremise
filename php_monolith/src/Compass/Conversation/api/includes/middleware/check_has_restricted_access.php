<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Request\PaymentRequiredException;
use BaseFrame\System\UserAgent;
use Tariff\Plan\MemberCount\Default\Plan;

/**
 * Устанавливает флаг возможности выполнить запрос к методу с ограниченным доступом.
 */
class Middleware_CheckHasRestrictedAccess implements \BaseFrame\Router\Middleware\Main {

	/**
	 * Проверяем, заблокировано ли пространство и можно ли выполнить метод
	 * @long
	 */
	public static function handle(\BaseFrame\Router\Request $request):\BaseFrame\Router\Request {

		if (COMPANY_ID < 1) {
			return $request;
		}

		$request->extra["space"]["is_restricted_access"] = false;
		$tariff_config                                   = getConfig("TARIFF");
		$not_restricted_platform_list                    = $tariff_config["member_count"]["not_restricted_platform_list"];

		// если не надо блокировать доступ на платформе - просто возвращаем запрос
		if (in_array(UserAgent::getPlatform(), $not_restricted_platform_list)) {
			return $request;
		}

		$tariff    = \CompassApp\Conf\Company::instance()->get("COMPANY_TARIFF");
		$plan_info = $tariff["plan_info"] ?? [];

		// устанавливаем тариф
		$request->extra["space"]["tariff"]               = Domain_SpaceTariff_Tariff::load($plan_info);
		$request->extra["space"]["is_restricted_access"] = false;

		/** @var Plan $member_count_plan */
		$member_count_plan = $request->extra["space"]["tariff"]->memberCount();

		// если тариф активен - возвращаем запрос
		if ($member_count_plan->isActive(time()) || !$member_count_plan->isRestricted(time())) {
			return $request;
		}

		// если можем вызвать метод - пропускаем запрос
		if (!$request->controller_class->isPaymentRequiredForMethodCall($request->method_name)) {

			$request->extra["space"]["is_restricted_access"] = true;
			return $request;
		}

		throw new PaymentRequiredException(PaymentRequiredException::RESTRICTED_ERROR_CODE, "payment required");
	}
}