<?php

namespace Compass\Company;

/**
 * Action для удалении аватара компании
 */
class Domain_Company_Action_ClearAvatar {

	/**
	 * Удаляем аватара компании
	 *
	 * @param int $user_id
	 *
	 * @return void
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $user_id):void {

		// очищаем аватар компании в pivot
		Gateway_Socket_Pivot::clearAvatarCompany($user_id);

		// отправляем ивент об удалении аватара компании
		Gateway_Bus_Sender::companyAvatarCleared();
	}
}
