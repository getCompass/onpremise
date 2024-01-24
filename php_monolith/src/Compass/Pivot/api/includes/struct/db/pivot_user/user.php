<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_user_{10m}.user_list_{1}
 */
class Struct_Db_PivotUser_User {

	public int    $user_id;
	public int    $npc_type;
	public int    $invited_by_partner_id;
	public int    $invited_by_user_id;
	public int    $last_active_day_start_at;
	public int    $created_at;
	public int    $updated_at;
	public int    $full_name_updated_at;
	public string $country_code;
	public string $full_name;
	public string $avatar_file_map;
	public array  $extra;

	public function __construct(
		int    $user_id,
		int    $npc_type,
		int    $invited_by_partner_id,
		int    $invited_by_user_id,
		int    $last_active_day_start_at,
		int    $created_at,
		int    $updated_at,
		int    $full_name_updated_at,
		string $country_code,
		string $full_name,
		string $avatar_file_map,
		array  $extra
	) {

		$this->user_id                  = $user_id;
		$this->npc_type                 = $npc_type;
		$this->invited_by_partner_id    = $invited_by_partner_id;
		$this->invited_by_user_id       = $invited_by_user_id;
		$this->last_active_day_start_at = $last_active_day_start_at;
		$this->created_at               = $created_at;
		$this->updated_at               = $updated_at;
		$this->full_name_updated_at     = $full_name_updated_at;
		$this->country_code             = $country_code;
		$this->full_name                = $full_name;
		$this->avatar_file_map          = $avatar_file_map;
		$this->extra                    = $extra;
	}
}