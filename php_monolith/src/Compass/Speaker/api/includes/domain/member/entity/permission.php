<?php

namespace Compass\Speaker;

/**
 * Класс для валидации разрешений пользователя
 */
class Domain_Member_Entity_Permission {

	/**
	 * Проверяем разрешение
	 *
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \returnException
	 */
	public static function check(int $user_id, int $method_version, string $permission_key):void {

		if ($method_version < 2) {
			return;
		}

		$member = Gateway_Bus_CompanyCache::getMember($user_id);

		try {

			// проверяем роль пользователя
			\CompassApp\Domain\Member\Entity\Member::assertUserAdministrator($member->role);
		} catch (\CompassApp\Domain\Member\Exception\IsNotAdministrator) {

			$member_permission = Gateway_Bus_CompanyCache::getConfigKey($permission_key);
			if ($member_permission->value["value"] === 0) {
				throw new Domain_Member_Exception_ActionNotAllowed("Action not allowed");
			}
		}
	}
}
