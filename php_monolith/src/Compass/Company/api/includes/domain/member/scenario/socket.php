<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\System\Locale;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Exception\AccountDeleted;
use CompassApp\Domain\Member\Exception\IsLeft;
use CompassApp\Domain\Member\Exception\UserIsGuest;

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
	 * @throws ParseFatalException
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

	/**
	 * Изменить права участнику
	 *
	 * @param int   $member_id
	 * @param array $permissions
	 *
	 * @return void
	 * @throws Domain_Member_Exception_IncorrectUserId
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 * @throws AccountDeleted
	 * @throws IsLeft
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function setPermissions(int $member_id, array $permissions):void {

		[$enabled_permission_list, $disabled_permission_list] = Permission::formatToList($permissions, Permission::CURRENT_PERMISSIONS_OUTPUT_SCHEMA_VERSION);

		// если участник удалил аккаунт или покинул пространство
		$member = Gateway_Bus_CompanyCache::getMember($member_id);
		\CompassApp\Domain\Member\Entity\Extra::assertIsNotDeleted($member->extra);
		Member::assertIsNotLeftRole($member->role);

		if (!Type_User_Main::isHuman($member->npc_type)) {
			throw new Domain_Member_Exception_IncorrectUserId("member is not human");
		}

		// если пользователь гость и получит новую роль, то сразу апгрейднем его от гостя к участнику
		$new_permissions_mask = Domain_User_Action_Member_SetPermissions::resolvePermissionsMask($member, false, $enabled_permission_list, $disabled_permission_list);
		$new_role             = Domain_User_Action_Member_SetPermissions::resolveRoleByPermissionsMask($new_permissions_mask);
		if ($member->role === Member::ROLE_GUEST && $member->role !== $new_role) {
			Domain_Member_Action_UpgradeGuest::do(0, $member_id, Locale::LOCALE_RUSSIAN);
		}

		// устанавливаем права
		[$new_role, $new_permissions_mask] = Domain_User_Action_Member_SetPermissions::do(
			$member, false, $enabled_permission_list, $disabled_permission_list
		);

		// отменяем всевозможные напоминания
		self::_undoNotifications($member, $new_role, $new_permissions_mask);

		// совершаем действия, не относящиеся напрямую к смене прав
		self::_afterSetPermissions($member, $new_role, $new_permissions_mask);
	}

	/**
	 * Отменить напоминания
	 *
	 * @param \CompassApp\Domain\Member\Struct\Main $member
	 * @param int                                   $role
	 * @param int                                   $permissions
	 *
	 * @return void
	 * @throws ParseFatalException
	 */
	protected static function _undoNotifications(\CompassApp\Domain\Member\Struct\Main $member, int $role, int $permissions):void {

		$notification_type_list = [];

		// если понизили - убираем уведомление
		if ($role === Member::ROLE_MEMBER && $member->role !== $role) {

			// убираем уведомление у всех администраторов от том, что был новый администратор
			Domain_Member_Action_UndoNotification::do($member->user_id, Domain_Member_Entity_Menu::ADMINISTRATOR_MEMBER);

			// прочитываем все уведомления у пользователя который больше не администратор
			Domain_Member_Action_UndoNotificationsForUser::do($member->user_id);
			return;
		}

		// если раньше у нас было право, по которому мы получали уведомление - убираем эти уведомления
		foreach (Domain_Member_Entity_Menu::NOTIFICATION_PERMISSION_REQUIREMENTS as $type => $permission_requirement) {

			if (Permission::hasPermission($member->permissions, $permission_requirement)
				&& !Permission::hasPermission($permissions, $permission_requirement)) {

				$notification_type_list[] = $type;
			}
		}

		// прочитываем все непрочитанные уведомления
		$read_notifications_count = Domain_Member_Entity_Menu::readAllUnreadNotificationsByType($member->user_id, $notification_type_list);

		// если были прочитанные уведомления
		if ($read_notifications_count > 0) {

			$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($member->user_id);
			Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, $member->user_id, [], $extra);
		}

		// шлем событие администратору о прочтении уведомлений
		$formatted_type_list      = [];
		$allowed_client_type_list = array_flip(Domain_Member_Entity_Menu::CLIENT_TYPE_LIST_SCHEMA);
		foreach ($notification_type_list as $type) {
			$formatted_type_list[] = $allowed_client_type_list[$type];
		}

		Gateway_Bus_Sender::memberMenuReadNotifications($member->user_id, $formatted_type_list);
	}

	/**
	 * Совершаем действия после смены прав (отправка событий, ws и т д)
	 *
	 * @param \CompassApp\Domain\Member\Struct\Main $member
	 * @param int                                   $role
	 * @param int                                   $permissions
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	protected static function _afterSetPermissions(\CompassApp\Domain\Member\Struct\Main $member, int $role, int $permissions):void {

		// если изменились роль или права
		if ($member->role != $role || $permissions != $member->permissions) {

			// пушим событие о изменении прав у пользователя
			Gateway_Event_Dispatcher::dispatch(Type_Event_Member_PermissionsChanged::create(
				$member->user_id, $member->role, $member->permissions, $role, $permissions), true);
		}

		// если сделали руководителем - добавляем уведомление о том, что появился новый администратор
		if ($role === Member::ROLE_ADMINISTRATOR && $member->role !== $role) {

			// добавляем уведомление всем администраторам о том, что появился новый администратор
			$company_name    = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::COMPANY_NAME)["value"];
			$avatar_color_id = \BaseFrame\Domain\User\Avatar::getColorByUserId($member->user_id);
			$extra           = new Domain_Member_Entity_Notification_Extra(
				0, $member->full_name, $company_name, $member->avatar_file_key, \BaseFrame\Domain\User\Avatar::getColorOutput($avatar_color_id)
			);
			Domain_Member_Action_AddNotification::do($member->user_id, Domain_Member_Entity_Menu::ADMINISTRATOR_MEMBER, $extra);
		}

		// отправляем ивент об изменении прав
		Gateway_Bus_Sender::permissionsChanged($member->user_id, $role, $permissions);

		// если это администратор, у которого отсутствует одно из прав, то в ws-событии для старых клиентов возвращаем как обычного участника
		$role_legacy = $role;
		if ($role == Member::ROLE_ADMINISTRATOR
			&& !Permission::hasPermissionList($permissions, Permission::OWNER_PERMISSION_LIST)) {

			$role_legacy = Member::ROLE_MEMBER;
		}

		// отправляем ws событие о том, что у пользователя изменена роль
		Gateway_Bus_Sender::userRoleChanged($member->user_id, $role_legacy);
	}
}
