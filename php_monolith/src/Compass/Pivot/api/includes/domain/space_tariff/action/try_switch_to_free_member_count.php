<?php

namespace Compass\Pivot;

/**
 * Действие для переключения тарифного плана на бесплатный, если это возможно.
 */
class Domain_SpaceTariff_Action_TrySwitchToFreeMemberCount {

	/**
	 * Пытается переключиться на бесплатный тариф
	 * @long
	 */
	public static function run(Struct_Db_PivotCompany_Company $space):void {

		// сразу проверим число участников, актуально это делать только для 11
		if (Domain_Company_Entity_Company::getMemberCount($space->extra) > 11) {
			return;
		}

		try {
			$tariff = Domain_SpaceTariff_Repository_Tariff::get($space->company_id);
		} catch (cs_CompanyIncorrectCompanyId) {

			// не получилось и ладно, просто ничего не делаем
			return;
		}

		// если ограничения на пространстве нет, то не пытаемся ничего сделать
		if ($tariff->memberCount()->getLimit() === \Tariff\Plan\MemberCount\OptionLimit::LIMIT_10
			|| $tariff->memberCount()->isActive(time())
			|| !$tariff->memberCount()->isRestricted(time())
		) {
			return;
		}

		// запускаем альтерацию
		$alteration = \Tariff\Plan\MemberCount\Alteration::make()
			->setMemberCount(\Tariff\Plan\MemberCount\OptionLimit::LIMIT_10)
			->setActions(\Tariff\Plan\BaseAlteration::CHANGE)
			->setAvailability(new \Tariff\Plan\AlterationAvailability(\Tariff\Plan\AlterationAvailability::AVAILABLE_DETACHED));

		try {

			// пытаемся провернуть альтерацию по увеличению числа участников
			// если не получается, то просто говорим, что нельзя расшириться
			Domain_SpaceTariff_Action_AlterMemberCount::run(
				0, $space->company_id, \Tariff\Plan\BaseAction::METHOD_DETACHED,
				$alteration, reason: Domain_SpaceTariff_Action_AlterMemberCount::REASON_SELF_USER_LEFT
			);

			$member_count = Domain_Company_Entity_Company::getMemberCount($space->extra);
			Type_System_Admin::log("switch_to_free", "переключили пространство {$space->company_id} с число пользователей: $member_count на бесплатный план");
		} catch (\Exception|\Error) {
			// не получилось и ладно, просто ничего не делаем
		}
	}

}