<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_userbot.userbot_list
 */
class Struct_Db_PivotUserbot_Userbot {

	public string $userbot_id;
	public int    $company_id;
	public int    $status;
	public int    $user_id;
	public int    $created_at;
	public int    $updated_at;
	public array  $extra;

	/**
	 * Struct_Db_PivotUserbot_Userbot constructor
	 *
	 */
	public function __construct(string $userbot_id, int $status, int $company_id, int $user_id, int $created_at, int $updated_at, array $extra) {

		$this->userbot_id = $userbot_id;
		$this->status     = $status;
		$this->company_id = $company_id;
		$this->user_id    = $user_id;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
		$this->extra      = $extra;
	}
}