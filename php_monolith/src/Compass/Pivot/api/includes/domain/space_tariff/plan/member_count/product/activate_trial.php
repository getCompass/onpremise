<?php

namespace Compass\Pivot;

/**
 * Класс для формирования товаров типа space_prolong.
 */
class Domain_SpaceTariff_Plan_MemberCount_Product_ActivateTrial {

	/** @var int[] фильтр лимита участников для триала */
	protected const _FILTER_MEMBER_COUNT_FOR_TRIAL = [
		\Tariff\Plan\MemberCount\OptionLimit::LIMIT_15,
	];

	/**
	 * Формирует альтерацию
	 */
	public static function makeAlteration(\Tariff\Plan\MemberCount\Default\Plan $current_plan, int $current_member_count):\Tariff\Plan\MemberCount\Alteration {

		$member_count_config = getConfig("TARIFF")["member_count"];
		$trial_period        = $member_count_config["trial_period"];

		// находим ближайшее значение, на которое можем прыгнуть
		$reduce_function = static function(int $carry, int $item) use ($current_member_count) {

			return $item >= $current_member_count && !in_array($item, self::_FILTER_MEMBER_COUNT_FOR_TRIAL) ? min($carry, $item) : $carry;
		};

		$next_member_count = array_reduce(\Tariff\Plan\MemberCount\OptionLimit::ALLOWED_VALUE_LIST, $reduce_function, PHP_INT_MAX);

		$alteration = \Tariff\Plan\MemberCount\Alteration::make()
			->setMemberCount($next_member_count)
			->setActions(\Tariff\Plan\BaseAlteration::CHANGE)
			->setAvailability(new \Tariff\Plan\AlterationAvailability(\Tariff\Plan\AlterationAvailability::AVAILABLE_DETACHED))
			->setTrialBehaviour(\Tariff\Plan\BaseAlteration::TRIAL_BEHAVIOUR_FREE_WHILE_ACTIVE);

		// если находимся на триале - завершаем
		if ($current_plan->isTrial(time())) {
			return $alteration;
		}

		// если нет - накидываем еще 30 дней триала
		return $alteration
			->setActions(\Tariff\Plan\BaseAlteration::CHANGE, \Tariff\Plan\BaseAlteration::PROLONG, \Tariff\Plan\BaseAlteration::ACTIVATE)
			->setProlongation(\Tariff\Plan\BaseAlteration::PROLONGATION_RULE_EXTEND, $trial_period)
			->setTrialBehaviour(\Tariff\Plan\BaseAlteration::TRIAL_BEHAVIOUR_FREE_WHILE_AVAILABLE);
	}
}