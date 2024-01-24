<?php

namespace Compass\Company;

/**
 * Класс для взаимодействия с гибернацией
 */
class Domain_System_Entity_Hibernation {

	/**
	 * если нужна гибернация
	 */
	public static function isNeedHibernate():bool {

		try {

			Gateway_Db_CompanyData_HibernationDelayTokenList::getOneAfterTime(time());

			// компанию не нужно усыплять
			return false;
		} catch (\cs_RowIsEmpty) {
		}

		// если есть активный тариф - не усыпляем
		$tariff_config = \CompassApp\Conf\Company::instance()->get("COMPANY_TARIFF");
		$plan_info     = $tariff_config["plan_info"] ?? [];
		$tariff        = Domain_SpaceTariff_Tariff::load($plan_info);

		if (!$tariff->memberCount()->isFree(time()) && $tariff->memberCount()->getActiveTill() > time() && !$tariff->memberCount()->isTrial(time())) {
			return false;
		}

		try {

			$hibernation_immunity_till = Domain_Company_Entity_Dynamic::get(Domain_Company_Entity_Dynamic::HIBERNATION_IMMUNITY_TILL);
		} catch (\cs_RowIsEmpty) {
			return true;
		}
		if ($hibernation_immunity_till->value > time()) {
			return false;
		}

		return true;
	}
}
