<?php

namespace Compass\Company;

/**
 * Сценарии для тарифов пространства
 */
class Domain_SpaceTariff_Scenario_Api {

	/**
	 * Получить тариф пространства
	 *
	 * @param int   $role
	 * @param array $plan_list
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \CompassApp\Domain\Member\Exception\IsNotAdministrator
	 * @throws \queryException
	 */
	public static function get(int $role, array $plan_list):array {

		$output    = [];
		$plan_list = array_flip($plan_list);

		\CompassApp\Domain\Member\Entity\Member::assertUserAdministrator($role);

		$plan_info     = [];
		$tariff_config = \CompassApp\Conf\Company::instance()->get("COMPANY_TARIFF");

		if (isset($tariff_config["plan_info"])) {
			$plan_info = $tariff_config["plan_info"];
		}

		$tariff = Domain_SpaceTariff_Tariff::load($plan_info);
		if (isset($plan_list["member_count"])) {

			$member_count = Domain_Company_Entity_Config::get(Domain_Company_Entity_Config::MEMBER_COUNT)["value"];
			$output       = Apiv2_Format::memberCountPlan($tariff->memberCount(), $member_count);
		}

		return Apiv2_Format::tariff($output);
	}

	/**
	 * открыт попап тарифов пространства
	 *
	 * @throws \CompassApp\Domain\Member\Exception\IsNotAdministrator
	 */
	public static function onShowcaseOpened(int $user_id, int $role):void {

		// проверяем, что у пользователя есть права
		\CompassApp\Domain\Member\Entity\Member::assertUserAdministrator($role);

		// отправляем запрос в интерком для отправки системного сообщения
		Gateway_Socket_Intercom::onTariffShowcaseOpened($user_id, getIp(), \BaseFrame\System\UserAgent::getUserAgent());
	}
}