<?php

namespace Compass\Pivot;

/**
 * Устанавливаем тип звуковых файлов
 */
class Domain_User_Action_Notifications_SetSoundType {

	/**
	 * Устанавливаем тип звуковых файлов
	 *
	 * @throws cs_NotificationUnsupportedSoundType
	 * @throws cs_UserNotHaveToken
	 * @throws \returnException
	 */
	public static function do(int $sound_type):void {

		// проверяем доступен ли такой тип звуков
		if (!Type_User_Notifications::isSoundTypeAllowed($sound_type)) {
			throw new cs_NotificationUnsupportedSoundType();
		}

		// устанавливаем тип звуков
		Type_User_Notifications::setSoundType(getDeviceId(), $sound_type);
	}
}