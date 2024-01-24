<?php

namespace Compass\Thread;

/**
 * Класс контроллер-тарифных планов.
 * Для company доступна загрузка только через конфиг.
 */
class Domain_SpaceTariff_Tariff extends \Tariff\Loader {

	protected const _DEFAULT_DATA = [

		self::MEMBER_COUNT_PLAN_KEY => [
			"plan_id"          => \Tariff\Plan\MemberCount\Default\Plan::PLAN_ID,
			"active_till"      => 0,
			"free_active_till" => 0,
			"option_list"      => [],
		],
	];

	/**
	 * Возвращает экземпляр класса.
	 */
	public static function load(array $data):static {

		return (new static())->_loadData($data);
	}
}