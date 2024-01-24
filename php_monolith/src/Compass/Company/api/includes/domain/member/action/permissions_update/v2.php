<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * Апдейт до второй версии прав
 */
class Domain_Member_Action_PermissionsUpdate_V2 implements Domain_Member_Action_PermissionsUpdate_Main {

	public const PERMISSIONS_VERSION = 2;

	/** @var array[] Правила конвертации старых прав в новые */
	protected const _CONVERT_PERMISSIONS_RULES = [

		Permission::HR_LEGACY => [
			Permission::MEMBER_INVITE,
			Permission::MEMBER_KICK,
			Permission::MEMBER_PROFILE_EDIT,
		],

		Permission::DEVELOPER_LEGACY => [
			Permission::BOT_MANAGEMENT,
		],

		Permission::ADMIN_LEGACY => [
			Permission::GROUP_ADMINISTRATOR,
		],
	];

	/**
	 * Обновить права
	 *
	 * @param array                 $member_list
	 * @param \BaseFrame\System\Log $log
	 * @param bool                  $is_dry
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \parseException
	 */
	public static function do(array $member_list, \BaseFrame\System\Log $log, bool $is_dry):array {

		foreach ($member_list as $key => $member) {

			[$member, $log] = self::_update($member, $log, $is_dry);

			$member_list[$key] = $member;
		}

		return [$member_list, $log];
	}

	/**
	 * Конвертируем права участника
	 *
	 * @param \CompassApp\Domain\Member\Struct\Main $member
	 * @param \BaseFrame\System\Log                 $log
	 * @param bool                                  $is_dry
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @long
	 */
	protected static function _update(\CompassApp\Domain\Member\Struct\Main $member, \BaseFrame\System\Log $log, bool $is_dry):array {

		$new_permission_list = [];

		if ($member->role === Member::ROLE_LEFT) {
			return [$member, $log];
		}

		// если пользователь уже руководитель - то выдаем ему все права и записываем изменения в базу
		if ($member->role === Member::ROLE_ADMINISTRATOR) {

			$member->permissions = Permission::addPermissionListToMask(Permission::DEFAULT, Permission::OWNER_PERMISSION_LIST);
			$log->addText("Пользователь $member->user_id имеет роль руководителя ($member->role). Выдаем все права ($member->permissions) (должно быть 511)...");
			$log = self::_updateRow($member, $log, $is_dry);
			return [$member, $log];
		}

		// конвертируем маску в список прав
		// функции все равно, какая модель, старая или новая, она просто составляет список прав в виде числ
		$current_permission_list = Permission::getPermissionList($member->permissions);

		//
		foreach ($current_permission_list as $permission) {

			if (isset(self::_CONVERT_PERMISSIONS_RULES[$permission])) {
				$new_permission_list = array_merge($new_permission_list, self::_CONVERT_PERMISSIONS_RULES[$permission]);
			}
		}

		// если какие-то права нашлись - делаем пользователя администратором и конвертируем права
		if (count($new_permission_list) > 0) {

			$member->role        = Member::ROLE_ADMINISTRATOR;
			$member->permissions = Permission::addPermissionListToMask(Permission::DEFAULT, $new_permission_list);

			$log->addText("Пользователь $member->user_id имеет роль участника ($member->role) с правами " .
				implode(",", $current_permission_list) . PHP_EOL .
				"Выдаем права " . implode(",", $new_permission_list) . "...");
			$log = self::_updateRow($member, $log, $is_dry);
		}

		return [$member, $log];
	}

	/**
	 * Обновляем запись в базе
	 *
	 * @param \CompassApp\Domain\Member\Struct\Main $member
	 * @param \BaseFrame\System\Log                 $log
	 * @param bool                                  $is_dry
	 *
	 * @return \BaseFrame\System\Log
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \parseException
	 */
	protected static function _updateRow(\CompassApp\Domain\Member\Struct\Main $member, \BaseFrame\System\Log $log, bool $is_dry):\BaseFrame\System\Log {

		$log->addText("Новые права для пользователя $member->user_id" . PHP_EOL .
			"Роль: $member->role" . PHP_EOL .
			"Права: $member->permissions");

		if ($is_dry) {
			return $log;
		}

		Gateway_Db_CompanyData_MemberList::set($member->user_id, [
			"role"        => $member->role,
			"permissions" => $member->permissions,
		]);

		// чистим кэш для участника
		Gateway_Bus_CompanyCache::clearMemberCacheByUserId($member->user_id);

		return $log;
	}

}