<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_sms_service.provider_list
 */
class Struct_PivotSmsService_Provider {

	public string $provider_id;
	public int    $is_available;
	public int    $is_deleted;
	public int    $created_at;
	public int    $updated_at;
	public array  $extra;

	/**
	 * Struct_PivotSmsService_Provider constructor.
	 *
	 */
	public function __construct(
		string $provider_id,
		int    $is_available,
		int    $is_deleted,
		int    $created_at,
		int    $updated_at,
		array  $extra
	) {

		$this->provider_id  = $provider_id;
		$this->is_available = $is_available;
		$this->is_deleted   = $is_deleted;
		$this->created_at   = $created_at;
		$this->updated_at   = $updated_at;
		$this->extra        = $extra;
	}
}