<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * Апдейт до четвертой версии прав
 */
class Domain_Member_Action_PermissionsUpdate_V4 implements Domain_Member_Action_PermissionsUpdate_Main {

	public const PERMISSIONS_VERSION = 4;

	/**
	 * выполнение скрипта на обновление прав
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

		$update_member_list = [];

		// для каждого участника
		foreach ($member_list as $member) {

			// обновляем
			[$is_updated, $member] = self::_update($member);

			if ($is_updated) {

				// записываем обновленные данные
				$update_member_list[] = $member;
			}
		}

		// если есть пользователи, которым нужно сбросить право или роль
		if ($update_member_list !== []) {
			self::_setMemberList($update_member_list, $log, $is_dry);
		}

		$log->addText("Прошлись по всем пользователям в company_id - " . COMPANY_ID . PHP_EOL);

		return [$update_member_list, $log];
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

		// если ЕСТЬ право SPACE_SETTINGS_LEGACY, но НЕТ права SPACE_SETTINGS
		if (Permission::hasPermission($member->permissions, Permission::SPACE_SETTINGS_LEGACY) && !Permission::hasPermission($member->permissions, Permission::SPACE_SETTINGS)) {

			// если при удалении неадминских прав и SPACE_SETTINGS_LEGACY у нас ничего не остается - мы обычный пользователь
			if (Permission::removePermissionListFromMask($member->permissions, array_merge(Permission::ALLOWED_PERMISSION_PROFILE_CARD_LIST, [Permission::SPACE_SETTINGS_LEGACY])) === Permission::DEFAULT) {

				$member->role        = Member::ROLE_MEMBER;
				$member->permissions = Permission::removePermissionListFromMask($member->permissions, [Permission::SPACE_SETTINGS_LEGACY]);
				return [true, $member];
			}

			// в данном случае удаляем только легаси право SPACE_SETTINGS_LEGACY
			$member->permissions = Permission::removePermissionListFromMask($member->permissions, [Permission::SPACE_SETTINGS_LEGACY]);
			return [true, $member];
		}

		return [false, $member];
	}

	/**
	 * Обновляем запись в базе
	 *
	 * @param array                 $member_list
	 * @param \BaseFrame\System\Log $log
	 * @param bool                  $is_dry
	 *
	 * @return \BaseFrame\System\Log
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \parseException
	 */
	protected static function _setMemberList(array $member_list, \BaseFrame\System\Log $log, bool $is_dry):\BaseFrame\System\Log {

		// если dry-run, то не обновляем базу
		if ($is_dry) {

			foreach ($member_list as $member) {
				$log->addText("DRY-RUN!!! Устанавливаем права/роль для пользователя user_id - " . $member->user_id . " в company_id - " . COMPANY_ID . PHP_EOL);
			}
			return $log;
		}

		foreach ($member_list as $member) {

			$log->addText("Устанавливаем права/роль для пользователя user_id - " . $member->user_id . " в company_id - " . COMPANY_ID . PHP_EOL);

			Gateway_Db_CompanyData_MemberList::set($member->user_id, [
				"role"        => $member->role,
				"permissions" => $member->permissions,
			]);

			// чистим кэш для участника
			Gateway_Bus_CompanyCache::clearMemberCacheByUserId($member->user_id);
		}

		return $log;
	}
}