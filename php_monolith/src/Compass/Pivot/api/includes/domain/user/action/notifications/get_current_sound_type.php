<?php

namespace Compass\Pivot;

/**
 * Получаем текущий тип звуковых файлов
 */
class Domain_User_Action_Notifications_GetCurrentSoundType {

	/**
	 * Получаем текущий тип звуковых файлов
	 *
	 */
	public static function do():int {

		// получаем текущий тип звуковых файлов
		return Type_User_Notifications::getSoundType(getDeviceId());
	}
}