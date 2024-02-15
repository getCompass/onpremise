<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Сценарии участников компании для API
 *
 * Class Domain_Member_Scenario_Api
 */
class Domain_Member_Scenario_Api {

	/**
	 * Смена короткого описания пользователя
	 *
	 * @param int    $user_id
	 * @param int    $role
	 * @param int    $permissions
	 * @param int    $modified_user_id
	 * @param string $description
	 * @param int    $method_version
	 *
	 * @throws Domain_User_Exception_IsAccountDeleted
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \busException
	 * @throws cs_IncorrectUserId
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 * @throws \queryException|\CompassApp\Domain\Member\Exception\ActionRestrictForUser
	 */
	public static function setDescription(int $user_id, int $role, int $permissions, int $modified_user_id, string $description, int $method_version):void {

		Member::assertUserNotGuest($role);
		Domain_Member_Entity_Permission::checkSpace($user_id, $method_version, Permission::IS_SET_MEMBER_PROFILE_ENABLED);
		Domain_User_Entity_Validator::assertValidUserId($modified_user_id);
		Domain_Member_Entity_Permission::checkUser($user_id, $modified_user_id, Permission::RESTRICT_DESCRIPTION_PROFILE_EDIT);
		$description = Domain_Member_Entity_Sanitizer::sanitizeDescription($description);

		try {
			$member = Gateway_Bus_CompanyCache::getMember($modified_user_id);
		} catch (\cs_RowIsEmpty) {
			throw new \cs_UserIsNotMember();
		}

		// если пытаемся сменить описание гостя
		Member::assertUserNotGuest($member->role);

		if ($user_id !== $modified_user_id) {
			Permission::assertCanEditMemberProfile($role, $permissions);
		}

		if (Member::isDisabledProfile($member->role)) {
			throw new \cs_UserIsNotMember();
		}
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($member->extra)) {
			throw new Domain_User_Exception_IsAccountDeleted("User delete his account");
		}

		Domain_Member_Action_SetDescription::do($modified_user_id, $description);
	}

	/**
	 * Устанавливаем статус-комментарий
	 *
	 * @param int    $user_id
	 * @param int    $method_version
	 * @param int    $role
	 * @param int    $permissions
	 * @param int    $modified_user_id
	 * @param string $status
	 *
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \busException
	 * @throws cs_IncorrectUserId
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function setStatus(int $user_id, int $method_version, int $role, int $permissions, int $modified_user_id, string $status):void {

		Domain_User_Entity_Validator::assertValidUserId($modified_user_id);
		$status = Domain_Member_Entity_Sanitizer::sanitizeStatus($status);
		Domain_Member_Entity_Permission::checkSpace($user_id, $method_version, Permission::IS_SET_MEMBER_PROFILE_ENABLED);
		Domain_Member_Entity_Permission::checkUser($user_id, $modified_user_id, Permission::RESTRICT_STATUS_PROFILE_EDIT);

		// если пользователь не меняет сам себя то надо проверить права
		if ($modified_user_id != $user_id) {

			// ругаемся, если другой человек пытается отредактировать статус-комментарий
			if (Type_System_Version::isEmployeeCardExtraPermissionFilter() && !isEmptyString($status)) {
				throw new \CompassApp\Domain\Member\Exception\ActionNotAllowed("company access limited");
			}

			// чекаем возможность нашего пользователя менять профиль
			Permission::assertCanEditMemberProfile($role, $permissions);
		}

		Domain_Member_Action_SetStatus::do($modified_user_id, $status);
	}

	/**
	 * Устанавливаем badge
	 *
	 * @param int          $user_id
	 * @param int          $role
	 * @param int          $permissions
	 * @param int          $modified_user_id
	 * @param int|false    $color_id
	 * @param string|false $content
	 * @param int          $method_version
	 *
	 * @throws Domain_User_Exception_IsAccountDeleted
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \CompassApp\Domain\Member\Exception\ActionRestrictForUser
	 * @throws \busException
	 * @throws cs_IncorrectUserId
	 * @throws cs_InvalidProfileBadge
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function setBadge(int $user_id, int $role, int $permissions, int $modified_user_id, int|false $color_id, string|false $content, int $method_version):void {

		Member::assertUserNotGuest($role);

		Domain_Member_Entity_Permission::checkSpace($user_id, $method_version, Permission::IS_SET_MEMBER_PROFILE_ENABLED);
		Domain_User_Entity_Validator::assertValidUserId($modified_user_id);
		Domain_Member_Entity_Permission::checkUser($user_id, $modified_user_id, Permission::RESTRICT_BADGE_PROFILE_EDIT);

		if ($color_id !== false || $content !== false) {

			$content = Domain_Member_Entity_Sanitizer::sanitizeBadgeContent($content);
			Domain_Member_Entity_Validator::assertBadge($color_id, $content);
		}

		try {
			$member = Gateway_Bus_CompanyCache::getMember($modified_user_id);
		} catch (\cs_RowIsEmpty) {
			throw new \cs_UserIsNotMember();
		}

		// проверяем, что пользователь которому меняем бейдж – не гость
		Member::assertUserNotGuest($member->role);

		if ($user_id !== $modified_user_id) {

			Permission::assertCanEditMemberProfile($role, $permissions);
		}

		if (Member::isDisabledProfile($member->role)) {
			throw new \cs_UserIsNotMember();
		}
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($member->extra)) {
			throw new Domain_User_Exception_IsAccountDeleted("User delete his account");
		}
		Domain_Member_Action_SetBadge::do($modified_user_id, $color_id, $content);
	}

	/**
	 * Устанавливаем mbti
	 *
	 * @param int    $user_id          пользователь который производит действие
	 * @param int    $modified_user_id пользователь над которым происходит действие
	 * @param string $mbti_type
	 *
	 * @throws Domain_User_Exception_IsAccountDeleted
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \busException
	 * @throws cs_IncorrectUserId
	 * @throws cs_InvalidProfileMbti
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 */
	public static function setMBTIType(int $user_id, int $role, int $permissions, int $modified_user_id, string $mbti_type):void {

		Domain_User_Entity_Validator::assertValidUserId($modified_user_id);

		if (mb_strlen($mbti_type) > 0) {
			Domain_Member_Entity_Validator::assertMBTIType($mbti_type);
		}

		try {
			$member = Gateway_Bus_CompanyCache::getMember($modified_user_id);
		} catch (\cs_RowIsEmpty) {
			throw new \cs_UserIsNotMember();
		}

		// если пользователь не имеет прав для установки mbti
		if (!Domain_User_Action_Member_CheckIsEditor::do($user_id, $role, $permissions, $modified_user_id)) {
			throw new \CompassApp\Domain\Member\Exception\ActionNotAllowed("company access limited");
		}
		if (Member::isDisabledProfile($member->role)) {
			throw new \cs_UserIsNotMember();
		}
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($member->extra)) {
			throw new Domain_User_Exception_IsAccountDeleted("User delete his account");
		}

		Domain_Member_Action_SetMbtiType::do($modified_user_id, $mbti_type);
	}

	/**
	 * Устанавливаем время присоединения к компании
	 *
	 * @param int $role
	 * @param int $permissions
	 * @param int $modified_user_id
	 * @param int $time
	 *
	 * @return float
	 * @throws Domain_User_Exception_IsAccountDeleted
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \busException
	 * @throws cs_DatesWrongOrder
	 * @throws cs_IncorrectUserId
	 * @throws cs_InvalidProfileJoinTime
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 */
	public static function setJoinTime(int $role, int $permissions, int $modified_user_id, int $time):float {

		Domain_User_Entity_Validator::assertValidUserId($modified_user_id);

		Domain_Member_Entity_Validator::assertJoinTime($time);
		$time = Domain_Member_Entity_Sanitizer::sanitizeJoinTime($time);

		try {
			$member = Gateway_Bus_CompanyCache::getMember($modified_user_id);
		} catch (\cs_RowIsEmpty) {
			throw new \cs_UserIsNotMember();
		}

		Permission::assertCanEditMemberProfile($role, $permissions);
		if (Member::isDisabledProfile($member->role)) {
			throw new \cs_UserIsNotMember();
		}
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($member->extra)) {
			throw new Domain_User_Exception_IsAccountDeleted("User delete his account");
		}

		Domain_Member_Action_SetJoinTime::do($modified_user_id, $time);

		return Domain_Member_Entity_EmployeeCard::calculateTotalWorkedTime($time, time());
	}

	/**
	 * Метод для получения пользователей
	 *
	 * @param array $batch_user_list
	 * @param array $need_user_id_list
	 *
	 * @return array
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_WrongSignature
	 * @throws \parseException
	 * @throws \returnException|cs_IncorrectUserId
	 */
	public static function getBatchingList(array $batch_user_list, array $need_user_id_list):array {

		Domain_Member_Entity_Validator::assertNeedUserIdList($need_user_id_list);

		// выбрасываем ошибку, если массив пользователей некорректен
		Domain_Member_Entity_Validator::assertBatchUserList($batch_user_list);

		// формируем массив пользователей для запроса
		$user_id_list = Domain_Member_Entity_Sanitizer::sanitizeUserList($batch_user_list, $need_user_id_list);
		$member_list  = Gateway_Bus_CompanyCache::getMemberList($user_id_list, false);

		$left_user_id_list = [];
		foreach ($member_list as $member) {

			if (Member::isDisabledProfile($member->role)) {
				$left_user_id_list[] = $member->user_id;
			}
		}

		return [$member_list, $left_user_id_list];
	}

	/**
	 * Сценарий получения пользователей с идентичным типом личности
	 *
	 * @param string $mbti_type
	 * @param int    $offset
	 * @param int    $count
	 *
	 * @return array
	 * @throws cs_InvalidProfileMbti
	 */
	public static function getListByMBTI(string $mbti_type, int $offset, int $count):array {

		Domain_Member_Entity_Validator::assertMBTIType($mbti_type);

		$offset = Domain_Member_Entity_Sanitizer::sanitizeGetMbtiListOffset($offset);
		$count  = Domain_Member_Entity_Sanitizer::sanitizeGetMbtiListCount($count);

		$user_list = Domain_Member_Action_GetByMbti::do($mbti_type, $offset, $count + 1);

		$has_next  = count($user_list) > $count;
		$user_list = array_slice($user_list, 0, $count);

		$action_user_id_list = [];
		foreach ($user_list as $user_info) {
			$action_user_id_list[] = $user_info->user_id;
		}
		return [$action_user_id_list, $has_next];
	}

	/**
	 * Смена короткого описания пользователя
	 *
	 * @param int          $role
	 * @param int          $permissions
	 * @param int          $modified_user_id пользователь над которым происходит действие
	 * @param string|false $description
	 * @param int|false    $time
	 * @param int|false    $color_id
	 * @param string|false $content
	 *
	 * @throws Domain_User_Exception_IsAccountDeleted
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \busException
	 * @throws cs_IncorrectUserId
	 * @throws cs_InvalidProfileBadge
	 * @throws cs_InvalidProfileJoinTime
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	public static function setDescriptionBadgeAndJoinTime(int $role, int $permissions, int $modified_user_id, string|false $description, int|false $time,
										int|false $color_id, string|false $content):void {

		Member::assertUserNotGuest($role);
		Domain_User_Entity_Validator::assertValidUserId($modified_user_id);

		if ($description !== false) {
			$description = Domain_Member_Entity_Sanitizer::sanitizeDescription($description);
		}

		if ($time !== false) {

			Domain_Member_Entity_Validator::assertJoinTime($time);
			$time = Domain_Member_Entity_Sanitizer::sanitizeJoinTime($time);
		}

		if ($color_id !== false || $content !== false) {

			$content = Domain_Member_Entity_Sanitizer::sanitizeBadgeContent($content);
			Domain_Member_Entity_Validator::assertBadge($color_id, $content);
		}

		try {
			$member = Gateway_Bus_CompanyCache::getMember($modified_user_id);
		} catch (\cs_RowIsEmpty) {
			throw new \cs_UserIsNotMember();
		}

		// если пытаемся изменить гостя
		Member::assertUserNotGuest($member->role);

		Permission::assertCanEditMemberProfile($role, $permissions);
		if (Member::isDisabledProfile($member->role)) {
			throw new \cs_UserIsNotMember();
		}
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($member->extra)) {
			throw new Domain_User_Exception_IsAccountDeleted("User delete his account");
		}

		Domain_Member_Action_SetDescriptionBadgeAndJoinTime::do($modified_user_id, $description, $time, $color_id, $content);
	}

	/**
	 * Получить список участников компании
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \ParamException
	 * @throws \cs_RowIsEmpty
	 * @throws \queryException
	 */
	public static function getList(int $user_id, int $role, int $method_version, string $query, int $limit, int $offset, array $filter_npc_type,
						 array $filter_role = [], string $sort_field = ""):array {

		// проверяем параметры
		[$sort_field, $filter_npc_type, $filter_role] = Domain_Member_Action_GetListByQuery::prepareParams($role,
			$limit, $offset, $sort_field, $filter_npc_type, $filter_role
		);

		Domain_Member_Entity_Permission::checkSpace($user_id, $method_version, Permission::IS_SHOW_COMPANY_MEMBER_ENABLED);

		// получаем список участников
		return Domain_Member_Action_GetListByQuery::do($query, $limit, $offset, $filter_npc_type, $filter_role, $sort_field, true);
	}

	/**
	 * Получить права
	 *
	 * @param int $member_id
	 * @param int $method_version
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \cs_RowIsEmpty
	 */
	public static function getPermissions(int $member_id, int $method_version):array {

		$permissions_output_version = match ($method_version) {
			1 => 1,
			2 => 2,
			default => Permission::CURRENT_PERMISSIONS_OUTPUT_SCHEMA_VERSION,
		};

		$member = Gateway_Bus_CompanyCache::getMember($member_id);

		return Apiv2_Format::permissions($member->user_id, $member->role, $member->permissions, $permissions_output_version);
	}

	/**
	 * Получить права для нескольких участников
	 *
	 * @param array $member_id_list
	 * @param int   $method_version
	 *
	 * @return array
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getPermissionsBatching(array $member_id_list, int $method_version):array {

		$member_list             = Gateway_Bus_CompanyCache::getMemberList($member_id_list);
		$member_permissions_list = [];

		$permissions_output_version = match ($method_version) {
			1 => 2,
			default => Permission::CURRENT_PERMISSIONS_OUTPUT_SCHEMA_VERSION,
		};

		foreach ($member_list as $member) {
			$member_permissions_list[] = Apiv2_Format::permissions($member->user_id, $member->role, $member->permissions, $permissions_output_version);
		}

		return $member_permissions_list;
	}

	/**
	 * Изменить права участнику
	 *
	 * @param int          $user_id
	 * @param int          $member_id
	 * @param string|false $role
	 * @param array        $permissions
	 * @param int          $method_version
	 *
	 * @return void
	 * @throws Domain_Member_Exception_IncorrectUserId
	 * @throws Domain_Member_Exception_SelfSetPermissions
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \CompassApp\Domain\Member\Exception\AccountDeleted
	 * @throws \CompassApp\Domain\Member\Exception\IsLeft
	 * @throws \busException
	 * @throws \cs_CompanyUserIncorrectRole
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \CompassApp\Domain\Member\Exception\UserIsGuest
	 *
	 */
	public static function setPermissions(int $user_id, int $member_id, string|false $role, array $permissions, int $method_version):void {

		[$enabled_permission_list, $disabled_permission_list] = self::_preparePermissionList($permissions, $method_version);

		// проверяем может ли устанавливать данные права себе
		if ($user_id === $member_id && !Permission::isCanSelfPermissionList(array_merge($enabled_permission_list, $disabled_permission_list))) {
			throw new Domain_Member_Exception_SelfSetPermissions("cant set permissions for self");
		}

		$member = Gateway_Bus_CompanyCache::getMember($member_id);

		Member::assertUserNotGuest($member->role);

		$role = match ($role) {
			false   => $method_version < 2 ? $member->role : false,
			default => Member::formatRoleToInt($role)
		};

		if ($role !== false) {
			Member::assertUserNotAllowedRole($role);
		}

		self::_throwIfMemberLeft($member);

		if (!Type_User_Main::isHuman($member->npc_type)) {
			throw new Domain_Member_Exception_IncorrectUserId("member is not human");
		}

		// устанавливаем права
		[$role, $permissions] = Domain_User_Action_Member_SetPermissions::do(
			$member, $role, $enabled_permission_list, $disabled_permission_list);

		// отменяем всевозможные напоминания
		self::_undoNotifications($member, $role, $permissions);

		// совершаем действия, не относящиеся напрямую к смене прав
		self::_afterSetPermissions($member, $role, $permissions, $user_id);
	}

	/**
	 * Подготовить список прав для работы сценария
	 *
	 * @param array $permissions
	 * @param int   $method_version
	 *
	 * @return array
	 */
	protected static function _preparePermissionList(array $permissions, int $method_version):array {

		$permissions_output_version = match ($method_version) {
			1       => 1,
			2       => 2,
			default => Permission::CURRENT_PERMISSIONS_OUTPUT_SCHEMA_VERSION,
		};

		// форматируем список прав от клиентов
		return Permission::formatToList($permissions, $permissions_output_version);
	}

	/**
	 * Изменить права в карточке пользователя
	 *
	 * @param int   $user_id
	 * @param int   $user_role
	 * @param int   $user_permissions
	 * @param int   $member_id
	 * @param array $permissions
	 *
	 * @return void
	 * @throws Domain_Member_Exception_IncorrectUserId
	 * @throws Domain_Member_Exception_SelfSetPermissions
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \CompassApp\Domain\Member\Exception\AccountDeleted
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \CompassApp\Domain\Member\Exception\IsLeft
	 * @throws \CompassApp\Domain\Member\Exception\PermissionNotAllowedSetAnotherAdministrator
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function setPermissionsProfileCard(int $user_id, int $user_role, int $user_permissions, int $member_id, array $permissions):void {

		// проверяем что не устанавливаем права себе в карточку
		if ($user_id === $member_id) {
			throw new Domain_Member_Exception_SelfSetPermissions("cant set permissions for self");
		}

		// проверяем, есть ли права редактировать чужие профили
		Permission::assertCanEditMemberProfile($user_role, $user_permissions);

		// проверяем что можно менять права в карточке пользователю, если он администратор
		$member = Gateway_Bus_CompanyCache::getMember($member_id);
		Permission::assertSetPermissionsProfileCardAnotherAdministrator($member);

		// проверяем что не кикнутый пользователь
		self::_throwIfMemberLeft($member);

		// проверяем что человек
		if (!Type_User_Main::isHuman($member->npc_type)) {
			throw new Domain_Member_Exception_IncorrectUserId("member is not human");
		}

		// форматируем список прав от клиентов
		[$enabled_permission_list, $disabled_permission_list] = Permission::formatProfileCardToList($permissions);

		// устанавливаем права
		$permissions = Domain_User_Action_Member_SetPermissionsProfileCard::do($member, $enabled_permission_list, $disabled_permission_list);

		// отправляем ивент об изменении прав
		Gateway_Bus_Sender::profileCardPermissionsChanged($member->user_id, Permission::formatProfileCardToOutput($permissions));
	}

	/**
	 * Отменить напоминания
	 *
	 * @param \CompassApp\Domain\Member\Struct\Main $member
	 * @param int                                   $role
	 * @param int                                   $permissions
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
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
	 * @param int                                   $user_id
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \parseException
	 */
	protected static function _afterSetPermissions(\CompassApp\Domain\Member\Struct\Main $member, int $role, int $permissions, int $user_id):void {

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
			Domain_Member_Action_AddNotification::do($member->user_id, Domain_Member_Entity_Menu::ADMINISTRATOR_MEMBER, $extra, exclude_receiver_id: $user_id);
			Type_Space_ActionAnalytics::send(COMPANY_ID, $user_id, Type_Space_ActionAnalytics::NEW_ADMINISTRATOR);
		} else {
			Type_Space_ActionAnalytics::send(COMPANY_ID, $user_id, Type_Space_ActionAnalytics::DISMISS_ADMINISTRATOR);
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

		// отправляем событие в CRM о смене роли у участника пространства
		Domain_Crm_Entity_Event_SpaceMemberRolePermissionsChanged::create(\CompassApp\System\Company::getCompanyId(), $member->user_id, $role, $permissions);
		Domain_Partner_Entity_Event_SpaceMemberRoleChanged::create(\CompassApp\System\Company::getCompanyId(), $member->user_id, $role);
	}

	/**
	 * Получить статистику экранного времени пользователя
	 *
	 * @param int $member_id
	 *
	 * @return array
	 * @throws Domain_Member_Exception_StatisticIsInfinite
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \CompassApp\Domain\Member\Exception\AccountDeleted
	 * @throws \cs_RowIsEmpty
	 * @throws \CompassApp\Domain\Member\Exception\IsLeft
	 */
	public static function getScreenTimeStat(int $member_id):array {

		// проверяем что пользователь есть в пространстве
		$member = Gateway_Bus_CompanyCache::getMember($member_id);

		self::_throwIfMemberLeft($member);

		// если статистика скрыта - возвращаем ошибку
		if (Permission::isAdministratorStatisticInfinite($member->role, $member->permissions)) {
			throw new Domain_Member_Exception_StatisticIsInfinite("statistic is hidden");
		}

		return Domain_Member_Action_GetScreenTimeStat::do($member_id);
	}

	/**
	 * Обновить профиль пользователя
	 *
	 * @throws Domain_Member_Exception_SetProfileRestrictForUser
	 * @throws Domain_User_Exception_IsAccountDeleted
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \busException
	 * @throws cs_IncorrectUserId
	 * @throws cs_InvalidProfileBadge
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function setProfile(int $user_id, int $role, int $permissions, int $modified_user_id, string|false $description, string|false $status,
						    int|false $badge_color_id, string|false $badge_content):void {

		// если гость пытается поменять бейдж или описание
		if ($description !== false || $badge_color_id !== false || $badge_content !== false) {
			Member::assertUserNotGuest($role);
		}

		// валидируем modified_user_id
		$member = self::_checkMember($modified_user_id);

		// проверяем ограничение на пространство
		Domain_Member_Entity_Permission::checkSpace($user_id, METHOD_VERSION_2, Permission::IS_SET_MEMBER_PROFILE_ENABLED);

		// массив для ошибки 2106006
		$output["updated"]    = [];
		$output["restricted"] = [];

		// если был передан description
		[$description, $output] = self::_checkDescription($user_id, $modified_user_id, $member->role, $description, $output);

		// если был передан status
		[$status, $output] = self::_checkStatus($user_id, $modified_user_id, $status, $output);

		// если был передан color_id или content
		[$badge_color_id, $badge_content, $output] = self::_checkBadge($user_id, $modified_user_id, $member->role, $badge_color_id, $badge_content, $output);

		// проверяем права
		if ($user_id !== $modified_user_id) {
			Permission::assertCanEditMemberProfile($role, $permissions);
		}

		// обновляем
		Domain_Member_Action_SetProfile::do($modified_user_id, $description, $status, $badge_color_id, $badge_content);

		if (count($output["restricted"]) > 0) {
			throw new Domain_Member_Exception_SetProfileRestrictForUser($output);
		}
	}

	/**
	 * Проверяем пользователя
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_IncorrectUserId
	 * @throws \cs_UserIsNotMember
	 * @throws Domain_User_Exception_IsAccountDeleted
	 */
	protected static function _checkMember(int $modified_user_id):\CompassApp\Domain\Member\Struct\Main {

		// валидируем user_id
		Domain_User_Entity_Validator::assertValidUserId($modified_user_id);

		// проверяем что пользователь участник компании
		try {
			$member = Gateway_Bus_CompanyCache::getMember($modified_user_id);
		} catch (\cs_RowIsEmpty) {
			throw new \cs_UserIsNotMember();
		}

		return $member;
	}

	/**
	 * Проверяем описание пользователя
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \cs_RowIsEmpty
	 */
	protected static function _checkDescription(int $user_id, int $modified_user_id, int $modified_user_role, string|false $description, array $output):array {

		if ($description !== false) {

			// если пользователь гость, то ругаемся
			Member::assertUserNotGuest($modified_user_role);

			try {

				Domain_Member_Entity_Permission::checkUser($user_id, $modified_user_id, Permission::RESTRICT_DESCRIPTION_PROFILE_EDIT);
				$description         = Domain_Member_Entity_Sanitizer::sanitizeDescription($description);
				$output["updated"][] = "description";
			} catch (\CompassApp\Domain\Member\Exception\ActionRestrictForUser) {

				$description            = false;
				$output["restricted"][] = "description";
			}
		}

		return [$description, $output];
	}

	/**
	 * Проверяем статус пользователя
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \cs_RowIsEmpty
	 */
	protected static function _checkStatus(int $user_id, int $modified_user_id, string|false $status, array $output):array {

		if ($status !== false) {

			try {

				Domain_Member_Entity_Permission::checkUser($user_id, $modified_user_id, Permission::RESTRICT_STATUS_PROFILE_EDIT);
				$status              = Domain_Member_Entity_Sanitizer::sanitizeStatus($status);
				$output["updated"][] = "status";
			} catch (\CompassApp\Domain\Member\Exception\ActionRestrictForUser) {

				$status                 = false;
				$output["restricted"][] = "status";
			}
		}

		return [$status, $output];
	}

	/**
	 * Проверяем бадж пользователя
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_InvalidProfileBadge
	 * @throws \cs_RowIsEmpty
	 */
	protected static function _checkBadge(int $user_id, int $modified_user_id, int $modified_user_role, int|false $badge_color_id, string|false $badge_content, array $output):array {

		if ($badge_color_id !== false || $badge_content !== false) {

			// если пользователь гость, то ругаемся
			Member::assertUserNotGuest($modified_user_role);

			try {

				$badge_content = Domain_Member_Entity_Sanitizer::sanitizeBadgeContent($badge_content);
				Domain_Member_Entity_Validator::assertBadgeColor($badge_color_id);
				Domain_Member_Entity_Permission::checkUser($user_id, $modified_user_id, Permission::RESTRICT_BADGE_PROFILE_EDIT);
				$output["updated"][] = "badge";
			} catch (\CompassApp\Domain\Member\Exception\ActionRestrictForUser) {

				$badge_content          = false;
				$badge_color_id         = false;
				$output["restricted"][] = "badge";
			}
		}

		return [$badge_color_id, $badge_content, $output];
	}

	/**
	 * Повысить пользователя Гостя до Участника в пространстве
	 *
	 * @throws Domain_Space_Exception_ActionRestrictedByTariff
	 * @throws \Throwable
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \CompassApp\Domain\Member\Exception\AccountDeleted
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \CompassApp\Domain\Member\Exception\IsLeft
	 * @throws \busException
	 * @throws cs_IncorrectUserId
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UserChangeSelfRole
	 * @throws \parseException
	 */
	public static function upgradeGuest(int $user_id, int $role, int $permissions, int $guest_id, string $locale):void {

		Domain_User_Entity_Validator::assertValidUserId($guest_id);
		Member::assertUserChangeSelfRole($user_id, $guest_id);
		Permission::assertCanManageAdministrators($role, $permissions);

		// проверяем блокировку
		Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::UPGRADE_GUEST);

		$guest_info = Gateway_Bus_CompanyCache::getMember($guest_id);

		// проверяем что не кикнутый пользователь
		self::_throwIfMemberLeft($guest_info);

		// проверяем, что пользователь имеет роль гостя
		Member::assertRole($guest_info, Member::ROLE_GUEST, new Domain_Member_Exception_UserHaveNotGuestRole());

		// повышаем гостя
		Domain_Member_Action_UpgradeGuest::do($user_id, $guest_id, $locale);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Выкинуть исключение, если участник покинул пространство
	 *
	 * @param \CompassApp\Domain\Member\Struct\Main $member
	 *
	 * @return void
	 * @throws \CompassApp\Domain\Member\Exception\AccountDeleted
	 * @throws \CompassApp\Domain\Member\Exception\IsLeft
	 */
	protected static function _throwIfMemberLeft(\CompassApp\Domain\Member\Struct\Main $member):void {

		\CompassApp\Domain\Member\Entity\Extra::assertIsNotDeleted($member->extra);
		Member::assertIsNotLeftRole($member->role);
	}
}
