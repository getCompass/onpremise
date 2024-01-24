<?php

namespace Compass\Pivot;

/**
 * Вспомогательный класс для работы с
 * альтерациями тарифных планов пространств.
 */
class Domain_SpaceTariff_Plan_MemberCount_ActivationResolver implements Domain_SpaceTariff_Entity_ActivationProductResolver {

	public const KEY = \Tariff\Loader::MEMBER_COUNT_PLAN_KEY;

	/**
	 * На основе goods_id определяет к каком тарифному плану принадлежит альтерация.
	 */
	public static function tryProduct(string $goods_id):Struct_SpaceTariff_ActivationItem|false {

		if (Domain_SpaceTariff_Plan_MemberCount_Product_ActivateDefault::checkGoodsId($goods_id)) {
			return Domain_SpaceTariff_Plan_MemberCount_Product_ActivateDefault::makeActivationItem($goods_id);
		}

		if (Domain_SpaceTariff_Plan_MemberCount_Product_ProlongDefault::checkGoodsId($goods_id)) {
			return Domain_SpaceTariff_Plan_MemberCount_Product_ProlongDefault::makeActivationItem($goods_id);
		}

		if (Domain_SpaceTariff_Plan_MemberCount_Product_ProlongTrial::checkGoodsId($goods_id)) {
			return Domain_SpaceTariff_Plan_MemberCount_Product_ProlongTrial::makeActivationItem($goods_id);
		}

		if (Domain_SpaceTariff_Plan_MemberCount_Product_ChangeDefault::checkGoodsId($goods_id)) {
			return Domain_SpaceTariff_Plan_MemberCount_Product_ChangeDefault::makeActivationItem($goods_id);
		}

		return false;
	}
}