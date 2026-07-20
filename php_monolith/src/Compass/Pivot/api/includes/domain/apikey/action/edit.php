<?php

namespace Compass\Pivot;

/**
 * Редактируем ключ
 */
class Domain_Apikey_Action_Edit
{
	/**
	 * Редактируем ключ
	 */
	public static function do(int $user_id, string $apikey, string $name, int $expires_at, array $scope_list): Struct_User_Apikey
	{

		return Gateway_Bus_Auth::edit($user_id, $apikey, $expires_at, $name, $scope_list);
	}
}
