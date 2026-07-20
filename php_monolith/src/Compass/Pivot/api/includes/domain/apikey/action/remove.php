<?php

namespace Compass\Pivot;

/**
 * Удаляем ключ
 */
class Domain_Apikey_Action_Remove
{
	/**
	 * Удаляем ключ
	 */
	public static function do(int $user_id, string $apikey): void
	{

		Gateway_Bus_Auth::remove($user_id, $apikey);
	}
}
