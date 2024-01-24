<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;

/**
 * Сценарии участников компании для socket методов
 */
class Domain_Member_Scenario_Socket {

	/**
	 * Действия при получении количества активных участников
	 *
	 * @param int  $from_date_at
	 * @param int  $to_date_at
	 * @param bool $is_assoc
	 *
	 * @return array
	 */
	public static function getActivityCountList(int $from_date_at, int $to_date_at, bool $is_assoc = false):array {

		// получаем записи
		return Gateway_Db_CompanySystem_MemberActivityList::getCountListByDate($from_date_at, $to_date_at, $is_assoc);
	}

	/**
	 * Обновляем права в пространстве
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function updatePermissions():void {

		$config = Domain_Company_Entity_Config::get(Domain_Company_Entity_Config::PERMISSIONS_VERSION);

		// если уже последняя версия прав - завершаем выполнение
		if ($config["value"] >= Domain_Member_Action_PermissionsUpdate_Handler::CURRENT_PERMISSIONS_VERSION) {
			return;
		}

		$member_list = Gateway_Db_CompanyData_MemberList::getAll();

		Domain_Member_Action_PermissionsUpdate_Handler::do($member_list, new \BaseFrame\System\Log(), false);

		// обновляем также и время покидания компании, если не вторая версия
		if ($config["value"] < Domain_Member_Action_PermissionsUpdate_V2::PERMISSIONS_VERSION) {
			Domain_Member_Action_SetDismissedAtAsLeftAt::do($member_list);
		}
	}

	/**
	 * Сценарий получения всех участников пространства за все время
	 *
	 * @return array
	 */
	public static function getAll():array {

		// возвращаем список пользователей с указанными ролями
		$roles = [
			Member::ROLE_LEFT,
			Member::ROLE_GUEST,
			Member::ROLE_MEMBER,
			Member::ROLE_ADMINISTRATOR,
		];
		return Gateway_Db_CompanyData_MemberList::getListByRoles($roles, 10000);
	}
}
