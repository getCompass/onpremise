<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * Апдейт до третьей версии прав
 */
class Domain_Member_Action_PermissionsUpdate_V3 implements Domain_Member_Action_PermissionsUpdate_Main {

	public const PERMISSIONS_VERSION = 3;

	/**
	 * Обновить права
	 *
	 * @param array                 $member_list
	 * @param \BaseFrame\System\Log $log
	 * @param bool                  $is_dry
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public static function do(array $member_list, \BaseFrame\System\Log $log, bool $is_dry):array {

		$updated_member_id_list = [];

		// для каждого участника
		foreach ($member_list as $key => $member) {

			// обновляем
			[$is_updated, $member] = self::_update($member);

			if ($is_updated) {

				// записываем обновленное значение
				$updated_member_id_list[] = $member->user_id;
				$member_list[$key]        = $member;
			}
		}

		// если есть пользователи, которым нужно сбросить роль, сбрасываем
		if ($updated_member_id_list !== []) {
			self::_setDefaultRoleForMemberList($updated_member_id_list, $log, $is_dry);
		}

		return [$member_list, $log];
	}

	/**
	 * Конвертируем права участника
	 *
	 * @param \CompassApp\Domain\Member\Struct\Main $member
	 *
	 * @return array
	 * @long
	 */
	protected static function _update(\CompassApp\Domain\Member\Struct\Main $member):array {

		// если пользователь не администратор - не обновляем участника
		if ($member->role !== Member::ROLE_ADMINISTRATOR) {
			return [false, $member];
		}

		// если администратор с правами - не обновляем участника
		if (!in_array($member->permissions, [Permission::DEFAULT, Permission::SPACE_SETTINGS_LEGACY], true)) {
			return [false, $member];
		}

		// сбрасываем роль администратора участнику
		$member->role        = Member::ROLE_MEMBER;
		$member->permissions = Permission::DEFAULT;

		return [true, $member];
	}

	/**
	 * Обновляем запись в базе
	 *
	 * @param array                 $member_id_list
	 * @param \BaseFrame\System\Log $log
	 * @param bool                  $is_dry
	 *
	 * @return \BaseFrame\System\Log
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	protected static function _setDefaultRoleForMemberList(array $member_id_list, \BaseFrame\System\Log $log, bool $is_dry):\BaseFrame\System\Log {

		$log->addText("Сбрасываем роли администратора для пользователей" . PHP_EOL . implode(", ", $member_id_list));

		// если dry-run, то не обновляем базу
		if ($is_dry) {
			return $log;
		}

		Gateway_Db_CompanyData_MemberList::setList($member_id_list, [
			"role"        => Member::ROLE_MEMBER,
			"permissions" => Permission::DEFAULT,
		]);

		// чистим кэш для участника
		Gateway_Bus_CompanyCache::clearMemberCacheByUserIdList($member_id_list);

		return $log;
	}

}