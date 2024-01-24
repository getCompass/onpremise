<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.member_notification_list
 */
class Struct_Db_CompanyData_MemberNotification {

	public int    $user_id;
	public int    $snoozed_until;
	public int    $created_at;
	public int    $updated_at;
	public string $token;
	public array  $device_list;
	public array  $extra;

	public function __construct(
		int    $user_id,
		int    $snoozed_until,
		int    $created_at,
		int    $updated_at,
		string $token,
		array  $device_list,
		array  $extra
	) {

		$this->user_id       = $user_id;
		$this->snoozed_until = $snoozed_until;
		$this->created_at    = $created_at;
		$this->updated_at    = $updated_at;
		$this->token         = $token;
		$this->device_list   = $device_list;
		$this->extra         = $extra;
	}
}