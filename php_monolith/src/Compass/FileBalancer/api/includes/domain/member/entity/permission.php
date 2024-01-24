<?php

namespace Compass\FileBalancer;

/**
 * Класс для валидации разрешений пользователя
 */
class Domain_Member_Entity_Permission {

	/**
	 * Проверяем разрешение
	 *
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws apiAccessException
	 * @throws busException
	 * @throws \cs_RowIsEmpty
	 * @throws returnException
	 */
	public static function check(int $user_id, int $method_version, int $file_source, string $permission_key):void {

		if (CURRENT_SERVER === PIVOT_SERVER) {
			return;
		}

		if ($file_source !== FILE_SOURCE_MESSAGE_VOICE) {
			return;
		}

		if ($method_version < 2) {
			return;
		}

		try {

			$member = Gateway_Bus_CompanyCache::getMember($user_id);
			\CompassApp\Domain\Member\Entity\Member::assertUserAdministrator($member->role);
		} catch (\CompassApp\Domain\Member\Exception\IsNotAdministrator) {

			$member_permission = Gateway_Bus_CompanyCache::getConfigKey($permission_key);
			if ($member_permission->value["value"] === 0) {
				throw new Domain_Member_Exception_ActionNotAllowed("Action not allowed");
			}
		}
	}
}
