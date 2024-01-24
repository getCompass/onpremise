<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_sms_service.observer_provider
 */
class Struct_PivotSmsService_ObserverProvider {

	public string $provider_id;
	public int    $need_work;
	public int    $created_at;
	public array  $extra;

	/**
	 * Struct_PivotSmsService_ObserveProviderTask constructor.
	 *
	 */
	public function __construct(
		string $provider_id,
		int    $need_work,
		int    $created_at,
		array  $extra
	) {

		$this->provider_id = $provider_id;
		$this->need_work   = $need_work;
		$this->created_at  = $created_at;
		$this->extra       = $extra;
	}
}