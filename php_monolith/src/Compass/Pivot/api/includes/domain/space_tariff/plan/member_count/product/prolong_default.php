<?php

namespace Compass\Pivot;

/**
 * Класс для формирования товаров типа space_prolong.
 */
class Domain_SpaceTariff_Plan_MemberCount_Product_ProlongDefault {

	protected const _ALLOWED_LIMIT_VALUES = \Tariff\Plan\MemberCount\OptionLimit::ALLOWED_VALUE_LIST;

	// набор доступных вариаций
	public const GOODS_ID_LABEL         = "space_prolong";
	public const BOUND_TARIFF_PLAN_TYPE = \Tariff\Loader::MEMBER_COUNT_PLAN_KEY;

	/**
	 * Проверяет соответствие goods_id для этого продукта.
	 */
	public static function checkGoodsId(string $goods_id):bool {

		$label       = static::GOODS_ID_LABEL;
		$valid_limit = implode("|", static::_ALLOWED_LIMIT_VALUES);

		return preg_match("#^\d+\.$label\.($valid_limit)\.\d+\.\d+$#", $goods_id);
	}

	/**
	 * Конвертирует goods_id в предмет альтерации тарифа member_count.
	 */
	public static function makeActivationItem(string $goods_id):Struct_SpaceTariff_MemberCount_ActivationItem {

		[$user_id, , $limit, $duration, $space_id] = explode(".", $goods_id);

		$alteration = static::makeAlteration($limit, $duration);
		return new Struct_SpaceTariff_MemberCount_ActivationItem($user_id, $space_id, static::BOUND_TARIFF_PLAN_TYPE, $alteration);
	}

	/**
	 * Генерирует элемент витрины.
	 */
	public static function makeShowcaseItem(Struct_SpaceTariff_MemberCount_ProductMaterial $material):Struct_SpaceTariff_MemberCount_ShowcaseItem {

		$customer_user_id = $material->customer_user_id;
		$space_id         = $material->space->company_id;
		$limit            = $material->slot_member_count;
		$duration         = $material->slot_duration;

		return new Struct_SpaceTariff_MemberCount_ShowcaseItem(
			static::makeGoodsID($customer_user_id, $space_id, $limit, $duration),
			static::makeAlteration($limit, $duration),
			$duration,
			$limit,
		);
	}

	/**
	 * Формирует goods_id для слота.
	 */
	public static function makeGoodsID(int $payer_user_id, int $space_id, int $limit, int $duration):string {

		return sprintf("%d.%s.%d.%d.%d", $payer_user_id, static::GOODS_ID_LABEL, $limit, $duration, $space_id);
	}

	/**
	 * Формирует альтерацию для слота.
	 */
	public static function makeAlteration(int $member_count, int $duration):\Tariff\Plan\MemberCount\Alteration {

		return \Tariff\Plan\MemberCount\Alteration::make()
			->setMemberCount($member_count)
			->setExpectedMemberCount($member_count)
			->setActions(\Tariff\Plan\BaseAlteration::PROLONG)
			->setProlongation(\Tariff\Plan\BaseAlteration::PROLONGATION_RULE_EXTEND, $duration * DAY1)
			->setAvailability(new \Tariff\Plan\AlterationAvailability(\Tariff\Plan\AlterationAvailability::AVAILABLE_REASON_REQUIRED));
	}
}