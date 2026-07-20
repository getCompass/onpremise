<?php

namespace Compass\Pivot;

/**
 * класс-агрегат для информации об Апи ключах
 */
class Struct_User_Apikey
{
	/**
	 * Struct_User_Apikey constructor.
	 */
	public function __construct(
		public int $user_id,
		public string $api_key,
		public int $expires_at,
		public string $name,
		public array $scope_list,
		public int $template_id,
	) {

	}
}
