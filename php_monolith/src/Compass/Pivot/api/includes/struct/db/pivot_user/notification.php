<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_user_{10m}.user_notification_list_{1}
 */
class Struct_Db_PivotUser_Notification {

	public int   $user_id;
	public int   $snoozed_until;
	public int   $created_at;
	public int   $updated_at;
	public array $device_list;
	public array $extra;

	public function __construct(
		int   $user_id,
		int   $snoozed_until,
		int   $created_at,
		int   $updated_at,
		array $device_list,
		array $extra
	) {

		$this->user_id       = $user_id;
		$this->snoozed_until = $snoozed_until;
		$this->created_at    = $created_at;
		$this->updated_at    = $updated_at;
		$this->device_list   = $device_list;
		$this->extra         = $extra;
	}
}