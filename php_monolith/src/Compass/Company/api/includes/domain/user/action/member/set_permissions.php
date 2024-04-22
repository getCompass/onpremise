<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\System\Locale;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Exception\IsLeft;
use CompassApp\Domain\Member\Struct\Main;

/**
 * Action для установки прав
 */
class Domain_User_Action_Member_SetPermissions {

	// связанные права - устанавливаются и убираются вместе
	protected const _RELATED_SPACE_SETTINGS_PERMISSIONS = [
		Permission::SPACE_SETTINGS,
		Permission::SPACE_SETTINGS_LEGACY,
	];

	/**
	 * Изменить роль пользователю
	 *
	 * @param Main      $member
	 * @param int|false $role
	 * @param array     $enabled_permission_list
	 * @param array     $disabled_permission_list
	 *
	 * @return array
	 * @throws LocaleTextNotFound
	 * @throws IsLeft
	 * @throws \busException
	 * @throws \parseException
	 */
	public static function do(Main $member, int|false $role, array $enabled_permission_list = [], array $disabled_permission_list = []):array {

		// проверяем, что пользователю можно менять роль (не кикнули)
		Member::assertIsNotLeftRole($member->role);

		$permissions = self::resolvePermissionsMask($member, $role, $enabled_permission_list, $disabled_permission_list);
		$role        = self::resolveRoleByPermissionsMask($permissions);

		// обновляем роль и права участника, если они реально изменились
		if ($member->role !== $role || $member->permissions !== $permissions) {

			self::_updateRow($member->user_id, $role, $permissions);

			// отправляем в premise-модуль ивент об изменении роли/прав пользователя
			Domain_Premise_Entity_Event_SpaceChangedMember::create($member->user_id, $role, $permissions);
		}

		self::_showAdministratorAnnouncements($member, $role);

		return [$role, $permissions];
	}

	/**
	 * Обновить роль пользователю
	 *
	 * @param int $permissions
	 *
	 * @return int
	 */
	public static function resolveRoleByPermissionsMask(int $permissions):int {

		// если при удалении неадминских прав у нас ничего не остается - мы обычный пользователь
		if (Permission::removePermissionListFromMask($permissions, Permission::ALLOWED_PERMISSION_PROFILE_CARD_LIST) === Permission::DEFAULT) {
			return Member::ROLE_MEMBER;
		}

		return Member::ROLE_ADMINISTRATOR;
	}

	/**
	 * Установить права в маске
	 *
	 * @param Main  $member
	 * @param int   $role
	 * @param array $enabled_permission_list
	 * @param array $disabled_permission_list
	 *
	 * @return int
	 */
	public static function resolvePermissionsMask(Main $member, int $role, array $enabled_permission_list, array $disabled_permission_list):int {

		$permissions = $member->permissions;

		// исключаем из списка активируемых те права, которые уже есть у участника, и не отключаем тех прав, которых и не было
		$current_permission_list  = Permission::getPermissionList($permissions);
		$enabled_permission_list  = array_diff($enabled_permission_list, $current_permission_list);
		$disabled_permission_list = array_intersect($disabled_permission_list, $current_permission_list);

		// отключаем легаси право "Настройки команды", если отключили новое право
		if (count(array_intersect($disabled_permission_list, self::_RELATED_SPACE_SETTINGS_PERMISSIONS)) > 0) {
			$disabled_permission_list = array_unique(array_merge($disabled_permission_list, self::_RELATED_SPACE_SETTINGS_PERMISSIONS));
		}

		// устанавливаем связанные права (легаси и новое "Настройки команды") для настройки пространства
		if (count(array_intersect($enabled_permission_list, self::_RELATED_SPACE_SETTINGS_PERMISSIONS)) > 0) {

			$enabled_permission_list  = array_unique(array_merge($enabled_permission_list, self::_RELATED_SPACE_SETTINGS_PERMISSIONS));
			$disabled_permission_list = array_diff($disabled_permission_list, self::_RELATED_SPACE_SETTINGS_PERMISSIONS);
		}

		$permissions = Permission::addPermissionListToMask($permissions, $enabled_permission_list);
		$permissions = Permission::removePermissionListFromMask($permissions, $disabled_permission_list);

		// если понизили до участника - забираем права, оставляя только неадминские если они были
		if ($role === Member::ROLE_MEMBER) {

			$profile_card_permission_list = [];
			$profile_card_permission_list = self::_setProfileCardPermissionListIfNeed($profile_card_permission_list, $permissions);
			$permissions                  = Permission::addPermissionListToMask(Permission::DEFAULT, $profile_card_permission_list);
		}

		return $permissions;
	}

	/**
	 * Устанавливаем права карточки пользователя (неадминские), если необходимо
	 *
	 * @return array
	 */
	protected static function _setProfileCardPermissionListIfNeed(array $profile_card_permission_list, int $permissions):array {

		if (Permission::hasPermission(Permission::RESTRICT_BADGE_PROFILE_EDIT, $permissions)) {
			$profile_card_permission_list[] = Permission::RESTRICT_BADGE_PROFILE_EDIT;
		}
		if (Permission::hasPermission(Permission::RESTRICT_STATUS_PROFILE_EDIT, $permissions)) {
			$profile_card_permission_list[] = Permission::RESTRICT_STATUS_PROFILE_EDIT;
		}
		if (Permission::hasPermission(Permission::RESTRICT_DESCRIPTION_PROFILE_EDIT, $permissions)) {
			$profile_card_permission_list[] = Permission::RESTRICT_DESCRIPTION_PROFILE_EDIT;
		}

		return $profile_card_permission_list;
	}

	/**
	 * Обновить запись в базе
	 *
	 * @param int $member_id
	 * @param int $role
	 * @param int $permissions
	 *
	 * @return void
	 * @throws \busException
	 * @throws \parseException
	 */
	protected static function _updateRow(int $member_id, int $role, int $permissions):void {

		Gateway_Db_CompanyData_MemberList::set($member_id, [
			"role"        => $role,
			"permissions" => $permissions,
		]);
	}

	/**
	 * Показываем администраторские анонсы
	 *
	 * @param Main $member
	 * @param int  $role
	 *
	 * @return void
	 */
	protected static function _showAdministratorAnnouncements(Main $member, int $role):void {

		$add_user_id_list    = [];
		$remove_user_id_list = [];

		// если повысили - добавляем анонсы
		if ($role == Member::ROLE_ADMINISTRATOR && $member->role != $role) {
			$add_user_id_list = [$member->user_id];
		}

		// если понизили - убираем анонсы
		if ($role == Member::ROLE_MEMBER && $member->role != $role) {
			$remove_user_id_list = [$member->user_id];
		}

		if (count($remove_user_id_list) < 1 && count($add_user_id_list) < 1) {
			return;
		}

		Gateway_Announcement_Main::changeReceiverUserList([
			\Service\AnnouncementTemplate\AnnouncementType::SPACE_TARIFF_EXPIRATION,
			\Service\AnnouncementTemplate\AnnouncementType::SPACE_TARIFF_EXPIRED,
		], $add_user_id_list, $remove_user_id_list);
	}

}