<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Класс-структура для таблицы cloud_company.userbot_list
 */
class Struct_Db_CloudCompany_Userbot {

	/**
	 * Struct_Db_CloudCompany_Userbot constructor.
	 */
	public function __construct(
		public string $userbot_id,
		public int    $status_alias,
		public int    $user_id,
		public string $smart_app_name,
		public int    $created_at,
		public int    $updated_at,
		public array  $extra,

	) {

	}
}