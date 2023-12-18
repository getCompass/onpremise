<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Exception\IsNotAdministrator;

/**
 * Сценарии для тарифов пространства
 */
class Domain_SpaceTariff_Scenario_Socket {

	/**
	 * Получить информацию для покупки товара
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \queryException
	 */
	public static function getInfoForPurchase(int $user_id):array {

		try {

			$member = Gateway_Bus_CompanyCache::getMember($user_id);
			\CompassApp\Domain\Member\Entity\Member::assertUserAdministrator($member->role);

			$is_administrator = true;
		} catch (\cs_RowIsEmpty|IsNotAdministrator) {
			$is_administrator = false;
		}

		$space_created_at = Domain_Company_Entity_Config::get(Domain_Company_Entity_Config::COMPANY_CREATED_AT)["value"];

		return [$is_administrator, $space_created_at];
	}
}