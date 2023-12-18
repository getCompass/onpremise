<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Экшн для отмены конвертации прав
 */
class Domain_Member_Action_UndoConvertPermissions {

	// до какой версии это даунгрейд
	public const TO_PERMISSIONS_VERSION = 1;

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
	 * Выполняем конвертацию
	 *
	 * @param array                 $member_list
	 * @param \BaseFrame\System\Log $log
	 * @param bool                  $is_dry
	 *
	 * @return \BaseFrame\System\Log
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function do(array $member_list, \BaseFrame\System\Log $log, bool $is_dry = true):\BaseFrame\System\Log {

		if ($is_dry) {
			$log->addText("!!!---DRY RUN---!!!");
		}

		$config = Domain_Company_Entity_Config::get(Domain_Company_Entity_Config::PERMISSIONS_VERSION);

		// если уже вторая версия прав - завершаем выполнение
		if ($config["value"] <= self::TO_PERMISSIONS_VERSION) {

			$log->addText("Версия прав в пространстве равняется 1, завершаю выполнение...");
			return $log;
		}

		foreach ($member_list as $member) {
			$log = self::_convert($member, $log, $is_dry);
		}

		if ($is_dry) {
			return $log;
		}

		// даунгрейдим версию прав до 1
		Domain_Company_Entity_Config::set(Domain_Company_Entity_Config::PERMISSIONS_VERSION, self::TO_PERMISSIONS_VERSION);

		return $log;
	}

	/**
	 * Конвертируем права участника
	 *
	 * @param \CompassApp\Domain\Member\Struct\Main $member
	 * @param \BaseFrame\System\Log                 $log
	 * @param bool                                  $is_dry
	 *
	 * @return \BaseFrame\System\Log
	 * @throws \busException
	 * @throws \parseException
	 */
	protected static function _convert(\CompassApp\Domain\Member\Struct\Main $member, \BaseFrame\System\Log $log, bool $is_dry):\BaseFrame\System\Log {

		// если пользователь администратор
		if ($member->role == Member::ROLE_ADMINISTRATOR) {

			if ($member->permissions == Permission::addPermissionListToMask(Permission::DEFAULT, Permission::ALLOWED_PERMISSION_LIST)) {

				$permission_list = [
					Permission::FULL_LEGACY,
					Permission::HR_LEGACY,
				];

				$member->permissions = Permission::addPermissionListToMask(Permission::DEFAULT, $permission_list);

				$log->addText("Пользователь $member->user_id имеет роль руководителя ($member->role). Выдаем права FULL и HR");
				self::_updateRow($member, $log, $is_dry);
				return $log;
			}

			// если пользователь - обычный участник
			$member->role = Member::ROLE_MEMBER;
			$permissions  = Permission::DEFAULT;

			// для каждого права смотрим, можно ли обратно его сконвертировать, и конвертим, если да
			foreach (self::_CONVERT_PERMISSIONS_RULES as $old_permission => $new_permissions) {

				if (Permission::hasPermissionList($member->permissions, $new_permissions)) {
					$permissions = Permission::addPermissionListToMask($permissions, [$old_permission]);
				}
			}

			$member->permissions = $permissions;
			$log->addText("Пользователь $member->user_id имеет роль участника ($member->role) и права $permissions. Выдаем права...");
			self::_updateRow($member, $log, $is_dry);
			return $log;
		}

		return $log;
	}

	/**
	 * Обновляем запись в базе
	 *
	 * @param \CompassApp\Domain\Member\Struct\Main $member
	 * @param \BaseFrame\System\Log                 $log
	 * @param bool                                  $is_dry
	 *
	 * @return \BaseFrame\System\Log
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