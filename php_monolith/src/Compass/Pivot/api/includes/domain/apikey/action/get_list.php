<?php

namespace Compass\Pivot;

/**
 * Получить список API ключей пользователя
 */
class Domain_Apikey_Action_GetList
{
	/**
	 * Получить список API ключей пользователя
	 *
	 * @return Struct_User_Apikey[]
	 */
	public static function do(int $user_id): array
	{

		return Gateway_Bus_Auth::getList($user_id);
	}
}
