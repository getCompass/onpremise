<?php

namespace Compass\Company;

/**
 * Класс для взаимодействия с сущностью access
 *
 * Class Domain_User_Entity_Access
 */
class Domain_User_Entity_Access {

	public const ACCESS_FULL       = "full"; // доступ к пространству есть
	public const ACCESS_RESTRICTED = "restricted"; // доступ ограченичен

	public const REASON_MEMBER_COUNT_TARIFF_PLAN_UNPAID = "member_count_tariff_plan_unpaid";

	/**
	 * Получаем состояние доступа в пространство
	 */
	public static function get():Struct_Access_Main {

		// загружаем конфиг с тарифом
		$tariff_config = \CompassApp\Conf\Company::instance()->get("COMPANY_TARIFF");
		$plan_info     = $tariff_config["plan_info"] ?? [];
		$tariff        = Domain_SpaceTariff_Tariff::load($plan_info);

		// если есть ограчение по плану участников, то так и возвращаем
		if (!$tariff->memberCount()->isActive(time()) && $tariff->memberCount()->isRestricted(time())) {

			return new Struct_Access_Main(
				self::ACCESS_RESTRICTED,
				self::REASON_MEMBER_COUNT_TARIFF_PLAN_UNPAID
			);
		}

		return new Struct_Access_Main(
			self::ACCESS_FULL,
			""
		);
	}
}
