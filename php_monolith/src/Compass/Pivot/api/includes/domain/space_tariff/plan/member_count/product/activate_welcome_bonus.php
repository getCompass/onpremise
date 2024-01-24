<?php

namespace Compass\Pivot;

/**
 * Активирует привественный бонус
 */
class Domain_SpaceTariff_Plan_MemberCount_Product_ActivateWelcomeBonus {

	// набор доступных вариаций
	public const GOODS_ID_LABEL         = "space_new";
	public const BOUND_TARIFF_PLAN_TYPE = \Tariff\Loader::MEMBER_COUNT_PLAN_KEY;

	/**
	 * Генерирует элемент витрины.
	 */
	public static function makeShowcaseItem(Struct_SpaceTariff_MemberCount_ProductMaterial $material):Struct_SpaceTariff_MemberCount_ShowcaseItem {

		return new Struct_SpaceTariff_MemberCount_ShowcaseItem(
			"",
			static::makeAlteration($material->slot_member_count, $material->slot_duration),
			$material->slot_duration,
			$material->slot_member_count,
			$material->space->created_at + DAY1 * 30
		);
	}

	/**
	 * Формирует альтерацию для слота.
	 */
	public static function makeAlteration(int $limit, int $duration):\Tariff\Plan\MemberCount\Alteration {

		return \Tariff\Plan\MemberCount\Alteration::make()
			->setMemberCount($limit)
			->setActions(\Tariff\Plan\BaseAlteration::PROLONG, \Tariff\Plan\BaseAlteration::CHANGE, \Tariff\Plan\BaseAlteration::ACTIVATE)
			->setProlongation(\Tariff\Plan\BaseAlteration::PROLONGATION_RULE_EXTEND, $duration * DAY1)
			->setAvailability(new \Tariff\Plan\AlterationAvailability(\Tariff\Plan\AlterationAvailability::AVAILABLE_REASON_REQUIRED))
			->setTrialBehaviour(\Tariff\Plan\BaseAlteration::TRIAL_BEHAVIOUR_ALLOWED_WHILE_AVAILABLE);
	}
}