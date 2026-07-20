<?php

namespace Compass\Pivot;

/**
 * Создаем апи ключ
 */
class Domain_Apikey_Action_Create
{
	/**
	 * Создаем апи ключ
	 */
	public static function do(int $user_id, string $name, int $expires_at, array $scope_list, int $template_id): Struct_User_Apikey
	{

		// создаем токен
		return Gateway_Bus_Auth::create($user_id, $expires_at, $name, $scope_list, $template_id);
	}
}
