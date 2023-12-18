<?php

namespace Compass\Pivot;

use Tariff\Plan\AlterationAvailability;

/**
 * Класс для формирования товаров типа space_extend.
 * У данного товара нет свойства продления длительности действия плана.
 */
class Domain_SpaceTariff_Plan_MemberCount_Product_ChangeDefault {

	protected const _ALLOWED_LIMIT_VALUES = \Tariff\Plan\MemberCount\OptionLimit::ALLOWED_VALUE_LIST;

	// набор доступных вариаций
	public const GOODS_ID_LABEL         = "space_extend";
	public const BOUND_TARIFF_PLAN_TYPE = \Tariff\Loader::MEMBER_COUNT_PLAN_KEY;

	/**
	 * Проверяет соответствие goods_id для этого продукта.
	 */
	public static function checkGoodsId(string $goods_id):bool {

		$label       = static::GOODS_ID_LABEL;
		$valid_limit = implode("|", static::_ALLOWED_LIMIT_VALUES);

		return preg_match("#^\d+\.$label\.($valid_limit)\.($valid_limit)\.\d+\.\d+$#", $goods_id);
	}

	/**
	 * Конвертирует goods_id в предмет альтерации тарифа member_count.
	 */
	public static function makeActivationItem(string $goods_id):Struct_SpaceTariff_MemberCount_ActivationItem {

		[$user_id, , $current_limit, $new_limit, $duration, $space_id] = explode(".", $goods_id);

		$alteration = static::makeAlteration($current_limit, $new_limit, $duration);
		return new Struct_SpaceTariff_MemberCount_ActivationItem($user_id, $space_id, static::BOUND_TARIFF_PLAN_TYPE, $alteration);
	}

	/**
	 * Генерирует элемент витрины.
	 */
	public static function makeShowcaseItem(Struct_SpaceTariff_MemberCount_ProductMaterial $material):Struct_SpaceTariff_MemberCount_ShowcaseItem {

		$customer_user_id = $material->customer_user_id;
		$space_id         = $material->space->company_id;
		$current_limit    = $material->current_plan->getLimit();
		$limit            = $material->slot_member_count;
		$duration         = ceil(($material->current_plan->getActiveTill() - time()) / DAY1);

		$alteration = static::makeAlteration($current_limit, $limit, $duration);

		// на всякий случай проверим
		// вдруг уже истек срок действия, такой элемент нельзя активировать
		if ($duration <= 0 && $limit !== 10) {
			$alteration->availability->arrange(new AlterationAvailability(AlterationAvailability::UNAVAILABLE_OUTDATED));
		}

		$duration = $duration > 0 ? $duration : $material->slot_duration;

		return new Struct_SpaceTariff_MemberCount_ShowcaseItem(
			static::makeGoodsID($customer_user_id, $space_id, $current_limit, $limit, $duration),
			$alteration,
			$duration,
			$limit,
		);
	}

	/**
	 * Формирует goods_id для слота.
	 */
	public static function makeGoodsID(int $payer_user_id, int $space_id, int $current_limit, int $new_limit, int $duration):string {

		return sprintf("%d.%s.%d.%d.%d.%d", $payer_user_id, static::GOODS_ID_LABEL, $current_limit, $new_limit, $duration, $space_id);
	}

	/**
	 * Формирует альтерацию для слота.
	 */
	public static function makeAlteration(int $current_limit, int $limit, int $duration):\Tariff\Plan\MemberCount\Alteration {

		return \Tariff\Plan\MemberCount\Alteration::make()
			->setMemberCount($limit)
			->setExpectedMemberCount($current_limit)
			->setActions(\Tariff\Plan\BaseAlteration::CHANGE)
			->setAvailability(new AlterationAvailability(AlterationAvailability::AVAILABLE_REASON_REQUIRED))
			->setTrialBehaviour(\Tariff\Plan\BaseAlteration::TRIAL_BEHAVIOUR_FREE_WHILE_ACTIVE)
			->setExpectedActiveTill(\Tariff\Plan\BaseAlteration::EXPECTED_ACTIVE_TILL_RULE_RANGE, DAY1 * $duration, DAY1 + 10);
	}
}