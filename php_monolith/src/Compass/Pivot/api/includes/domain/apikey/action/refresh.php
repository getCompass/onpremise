<?php

namespace Compass\Pivot;

/**
 * Пересоздаем ключ
 */
class Domain_Apikey_Action_Refresh
{
	/**
	 * Пересоздаем ключ
	 */
	public static function do(int $user_id, string $apikey): Struct_User_Apikey
	{

		// создаем токен
		return Gateway_Bus_Auth::refresh($user_id, $apikey);
	}
}
