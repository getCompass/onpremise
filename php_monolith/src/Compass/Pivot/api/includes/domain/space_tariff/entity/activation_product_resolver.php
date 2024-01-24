<?php

namespace Compass\Pivot;

/**
 * Интерфейс определителя типа товара на основе goods_id.
 */
interface Domain_SpaceTariff_Entity_ActivationProductResolver {

	/**
	 * На основе goods_id определяет к каком тарифному плану принадлежит альтерация.
	 */
	public static function tryProduct(string $goods_id):Struct_SpaceTariff_ActivationItem|false;
}