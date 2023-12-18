<?php

namespace Compass\Pivot;

/**
 * Класс для управления витринами тарифных планов пространства.
 */
class Domain_SpaceTariff_Entity_ShowcaseManager {

	/**
	 * Формирует витрину указанного типа.
	 *
	 * @throws Domain_SpaceTariff_Exception_InvalidShowcaseType
	 * @throws Domain_SpaceTariff_Exception_UnsupportedShowcaseVersion
	 */
	public static function makeShowcase(int $user_id, Struct_SpaceTariff_SpaceInfo $space_info, string $type, int $version):Struct_SpaceTariff_Showcase {

		try {

			// получаем тариф для чтения
			$tariff = Domain_SpaceTariff_Repository_Tariff::get($space_info->space->company_id);
		} catch (cs_CompanyIncorrectCompanyId) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("company not found");
		}

		if ($type === Domain_SpaceTariff_Plan_MemberCount_Showcase::TYPE) {
			return static::_makeMemberCountShowcase($user_id, $space_info, $version, $tariff->memberCount());
		}

		throw new Domain_SpaceTariff_Exception_InvalidShowcaseType("passed unknown showcase type $type");
	}

	/**
	 * Возвращает элементы витрины для тарифа слотов пользователей в пространстве.
	 * @throws Domain_SpaceTariff_Exception_UnsupportedShowcaseVersion
	 */
	protected static function _makeMemberCountShowcase(int $user_id, Struct_SpaceTariff_SpaceInfo $space_info, int $version, \Tariff\Plan\MemberCount\MemberCount $current_plan):Struct_SpaceTariff_MemberCount_Showcase {

		Domain_SpaceTariff_Plan_MemberCount_Showcase::assertVersion($version);

		$action_list = Domain_SpaceTariff_Plan_MemberCount_Showcase::getShowcaseActions($user_id, $space_info->space, $version, $current_plan);
		$promo_list  = Domain_SpaceTariff_Plan_MemberCount_Showcase::getShowcasePromo($user_id, $space_info, $current_plan);

		return new Struct_SpaceTariff_MemberCount_Showcase(Domain_SpaceTariff_Plan_MemberCount_Showcase::TYPE, (object) $action_list, (object) $promo_list);
	}
}
