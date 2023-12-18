<?php

namespace Compass\Pivot;

/**
 * Вспомогательный класс для работы с
 * активациями покупок/промо тарифных планов пространств.
 */
class Domain_SpaceTariff_Entity_ActivationResolver {

	/** @var Domain_SpaceTariff_Entity_ActivationProductResolver[] */
	protected const _POSSIBLE_RESOLVER_CLASS_LIST = [
		Domain_SpaceTariff_Plan_MemberCount_ActivationResolver::class,
	];

	/**
	 * Определяет, какой тип тарифного плана должен активироваться указанным goods_id.
	 */
	public static function resolve(string $goods_id):Struct_SpaceTariff_ActivationItem|false {

		foreach (static::_POSSIBLE_RESOLVER_CLASS_LIST as $possible_resolver_class) {

			$result = $possible_resolver_class::tryProduct($goods_id);

			if ($result !== false) {
				return $result;
			}
		}

		return false;
	}
}
