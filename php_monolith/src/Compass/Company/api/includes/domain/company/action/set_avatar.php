<?php

namespace Compass\Company;

/**
 * Action для изменения цвета аватарки компании
 */
class Domain_Company_Action_SetAvatar {

	/**
	 * Изменяем цвет аватарки
	 *
	 * @throws \parseException
	 */
	public static function do(int $user_id, int $avatar_color_id):void {

		Gateway_Socket_Pivot::setAvatar($user_id, $avatar_color_id);

		Gateway_Bus_Sender::companyProfileChanged(false, $avatar_color_id, false);
	}
}
