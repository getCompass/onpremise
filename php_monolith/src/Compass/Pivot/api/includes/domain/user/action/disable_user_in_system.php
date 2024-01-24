<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * Действие, блокирующее пользователя в системе.
 * Первый этап в блокировке пользователя.
 */
class Domain_User_Action_DisableUserInSystem {

	/**
	 * Первый этап блокировки пользователя в системе.
	 * Отрезаем ему доступ к приложению.
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function run(int $user_id):void {

		// помечаем пользователь заблокированным и инвалидируем его сессии
		try {
			Domain_User_Action_DisableProfile::do($user_id);
		} catch (cs_UserAlreadyBlocked) {
			throw new ParamException("user is already blocked");
		}
		Domain_User_Action_InvalidateSessions::do($user_id);

		// ставим задачку на продолжение работы
		Domain_User_Action_KickUserFromSystem::run($user_id);
	}
}
