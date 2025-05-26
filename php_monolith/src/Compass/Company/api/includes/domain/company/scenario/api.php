<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Exception\ActionNotAllowed;

/**
 * Сценарии компании для API
 */
class Domain_Company_Scenario_Api {

	/**
	 * Сценарий изменения имени компании
	 *
	 * @param int    $user_id
	 * @param int    $role
	 * @param int    $permissions
	 * @param string $name
	 *
	 * @return string
	 * @throws ActionNotAllowed
	 * @throws cs_CompanyIncorrectName
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function setName(int $user_id, int $role, int $permissions, string $name):string {

		$name = Domain_Company_Entity_Sanitizer::sanitizeCompanyName($name);
		Domain_Company_Entity_Validator::assertIncorrectName($name);

		Permission::assertCanEditSpaceSettings($role, $permissions);

		Domain_Company_Action_SetName::do($user_id, $name);

		return $name;
	}

	/**
	 * Сценарий изменения цвета аватарки компании
	 *
	 * @param int $user_id
	 * @param int $role
	 * @param int $permissions
	 * @param int $avatar_color_id
	 *
	 * @throws ActionNotAllowed
	 * @throws cs_CompanyIncorrectAvatarColorId
	 * @throws \parseException
	 */
	public static function setAvatar(int $user_id, int $role, int $permissions, int $avatar_color_id):void {

		Domain_Company_Entity_Validator::assertIncorrectAvatarColorId($avatar_color_id);

		// проверяем может ли пользователь выполнить это действие
		Permission::assertCanEditSpaceSettings($role, $permissions);

		Domain_Company_Action_SetAvatar::do($user_id, $avatar_color_id);
	}

	/**
	 * Сценарий изменения основных данных профиля компании
	 *
	 * @param int          $user_id
	 * @param int          $role
	 * @param int          $permissions
	 * @param string|false $name
	 * @param int|false    $avatar_color_id
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ActionNotAllowed
	 * @throws cs_CompanyIncorrectAvatarColorId
	 * @throws cs_CompanyIncorrectName
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function setBaseInfo(int $user_id, int $role, int $permissions, string|false $name, int|false $avatar_color_id):array {

		if ($name === false && $avatar_color_id === false) {
			throw new ParamException("params are empty");
		}

		if ($name !== false) {

			// очищаем новое имя компании, проверяем, что оно корректное
			$name = Domain_Company_Entity_Sanitizer::sanitizeCompanyName($name);
			Domain_Company_Entity_Validator::assertIncorrectName($name);
		}

		if ($avatar_color_id !== false) {

			// проверяем на корректность новый цвет аватарки компании
			Domain_Company_Entity_Validator::assertIncorrectAvatarColorId($avatar_color_id);
		}

		// проверяем может ли пользователь выполнить это действие
		Permission::assertCanEditSpaceSettings($role, $permissions);

		[$current_name, $current_avatar_color_id] = Domain_Company_Action_SetBaseInfo::do($user_id, $name, $avatar_color_id);

		return [$current_name, $current_avatar_color_id];
	}

	/**
	 * Сценарий настройки отображения сообщения в пуше
	 *
	 * @param int $role
	 * @param int $permissions
	 * @param int $value
	 *
	 * @throws ParseFatalException
	 * @throws ActionNotAllowed
	 * @throws cs_InvalidConfigValue
	 * @throws \queryException
	 */
	public static function setPushBodyDisplayConfig(int $role, int $permissions, int $value):void {

		Domain_Company_Entity_Config::edit($role, $permissions, Domain_Company_Entity_Config::PUSH_BODY_DISPLAY_KEY, $value);

		Gateway_Bus_Sender::pushBodyDisplayChanged($value);
	}

	/**
	 * Сценарий получения настройки отображения сообщения в пуше
	 *
	 * @return int
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function getPushBodyDisplayConfig():int {

		// достаём значение
		$config = Domain_Company_Entity_Config::get(Domain_Company_Entity_Config::PUSH_BODY_DISPLAY_KEY);

		return $config["value"];
	}

	/**
	 * Сценарий настройки карточки (базовая/расширенная)
	 *
	 * @param int $user_id
	 * @param int $role
	 * @param int $permissions
	 * @param int $is_enabled
	 *
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws ActionNotAllowed
	 * @throws cs_InvalidConfigValue
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function setExtendedEmployeeCard(int $user_id, int $role, int $permissions, int $is_enabled):void {

		$is_edited = Domain_Company_Entity_Config::edit($role, $permissions, Domain_Company_Entity_Config::MODULE_EXTENDED_EMPLOYEE_CARD_KEY, $is_enabled);

		if ($is_edited) {
			Domain_Company_Action_SendExtendedCardEvent::do($user_id, $is_enabled);
		}
	}

	/**
	 * Получение значения настройки расширенной карточки
	 *
	 * @return int
	 */
	public static function getExtendedEmployeeCard():int {

		return Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::MODULE_EXTENDED_EMPLOYEE_CARD_KEY)["value"] ?? 0;
	}

	/**
	 * Форматирование списка пользователей чтобы остались только ID
	 *
	 * @param array $user_id_list
	 *
	 * @return array
	 */
	protected static function _formatUserIdList(array $user_id_list):array {

		$formated_id_list = [];

		foreach ($user_id_list as $value) {
			$formated_id_list[] = (int) $value;
		}
		return $formated_id_list;
	}

	/**
	 * Сценарий изменения информации компании
	 */
	public static function changeInfo(int $user_id, int $role, int $permissions, string|false $name, string|false $avatar_file_key):array {

		if ($name === false && $avatar_file_key === false) {
			throw new Domain_Company_Exception_ParamsIsEmpty("Params is empty");
		}

		if ($name !== false) {

			// очищаем новое имя компании, проверяем, что оно корректное
			$name = Domain_Company_Entity_Sanitizer::sanitizeCompanyName($name);
			Domain_Company_Entity_Validator::assertIncorrectName($name);
		}

		// проверяем может ли пользователь выполнить это действие
		Permission::assertCanEditSpaceSettings($role, $permissions);

		return Domain_Company_Action_ChangeInfo::do($user_id, $name, $avatar_file_key);
	}

	/**
	 * Сценарий удаления аватара компании
	 *
	 * @param int $user_id
	 * @param int $role
	 * @param int $permissions
	 *
	 * @return void
	 * @throws ActionNotAllowed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function clearAvatar(int $user_id, int $role, int $permissions):void {

		// проверяем может ли пользователь выполнить это действие
		Permission::assertCanEditSpaceSettings($role, $permissions);

		Domain_Company_Action_ClearAvatar::do($user_id);
	}

	/**
	 * Сценарий смены настроек оповещений в главном чате
	 *
	 * @param int $role
	 * @param int $permissions
	 * @param int $value
	 *
	 * @return void
	 * @throws ParseFatalException
	 * @throws ActionNotAllowed
	 * @throws cs_InvalidConfigValue
	 * @throws \queryException
	 */
	public static function setGeneralChatNotifications(int $role, int $permissions, int $value):void {

		$is_edited = Domain_Company_Entity_Config::edit(
			$role, $permissions, Domain_Company_Entity_Config::GENERAL_CHAT_NOTIFICATIONS, $value);

		if (!$is_edited) {
			return;
		}

		// отправляем событие о переключении настройки
		Gateway_Bus_Sender::generalChatNotificationSettingsChanged($value);
	}

	/**
	 * Сценарий смены настройки добавлять ли пользователя в Главный чат при вступлении
	 *
	 * @param int $role
	 * @param int $permissions
	 * @param int $value
	 *
	 * @return void
	 * @throws ParseFatalException
	 * @throws ActionNotAllowed
	 * @throws cs_InvalidConfigValue
	 * @throws \queryException
	 */
	public static function setAddToGeneralChatOnHiring(int $role, int $permissions, int $value):void {

		$is_edited = Domain_Company_Entity_Config::edit(
			$role, $permissions, Domain_Company_Entity_Config::ADD_TO_GENERAL_CHAT_ON_HIRING, $value);

		if (!$is_edited) {
			return;
		}

		// отправляем событие о переключении настройки
		Gateway_Bus_Sender::addToGeneralChatOnHiringSettingChanged($value);
	}

	/**
	 * Сценарий смены настройки показывать статус просмотра сообщения
	 *
	 * @param int $role
	 * @param int $permissions
	 * @param int $value
	 *
	 * @return void
	 * @throws ParseFatalException
	 * @throws ActionNotAllowed
	 * @throws cs_InvalidConfigValue
	 * @throws \queryException
	 */
	public static function setShowMessageReadStatus(int $role, int $permissions, int $value):void {

		$is_edited = Domain_Company_Entity_Config::edit(
			$role, $permissions, Domain_Company_Entity_Config::SHOW_MESSAGE_READ_STATUS, $value);

		if (!$is_edited) {
			return;
		}

		// чистим кеш для ключа
		Gateway_Bus_CompanyCache::clearConfigCacheByKey(Domain_Company_Entity_Config::SHOW_MESSAGE_READ_STATUS);

		// отправляем событие о переключении настройки
		Gateway_Bus_Sender::showMessageReadStatusConfigChanged($value);
	}

	/**
	 * Получить данные активной компании
	 *
	 * @param int $user_id
	 * @param int $role
	 * @param int $permissions
	 *
	 * @return Struct_Domain_Company_ActivityData
	 * @throws ParseFatalException
	 * @throws \returnException
	 */
	public static function getActivityData(int $user_id, int $role, int $permissions):Struct_Domain_Company_ActivityData {

		$premium_payment_request = Domain_Premium_Entity_PaymentRequest::get($user_id);

		// получаем время, до которого текущий запрос активен
		$premium_payment_request_active_till = $premium_payment_request
			? Domain_Premium_Entity_PaymentRequest::getRequestActiveTill($premium_payment_request->requested_at)
			: 0;

		$general_group_conversation_config = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::GENERAL_CONVERSATION_KEY_NAME);
		$general_group_conversation_map    = $general_group_conversation_config["value"];
		$general_group_conversation_key    = \CompassApp\Pack\Conversation::doEncrypt($general_group_conversation_map);

		$common_activity_data = new Struct_Domain_Company_CommonActivityData(
			$premium_payment_request_active_till,
			$general_group_conversation_key
		);

		$owner_activity_data = null;

		// если пользователь имеет доступ к меню премиума - отдаем еще и количество непрочитанных запросов на оплату премиума
		if (Permission::canEditSpaceSettings($role, $permissions)) {

			$premium_payment_request_menu_count = Domain_Premium_Entity_PaymentRequestMenu::getUnreadCount($user_id);
			$owner_activity_data                = new Struct_Domain_Company_OwnerActivityData(
				$premium_payment_request_menu_count
			);
		}

		return new Struct_Domain_Company_ActivityData(
			$common_activity_data,
			$owner_activity_data,
		);
	}

	/**
	 * Изменяем ограничения роли участника
	 */
	public static function setMemberPermissions(int $user_id, array $member_permissions_list):void {

		// проверяем может ли пользователь выполнить это действие
		$member = Domain_User_Action_Member_GetShort::do($user_id);
		Permission::assertCanEditSpaceSettings($member->role, $member->permissions);

		// валидируем переданный конфиг настроек
		if ((count($member_permissions_list) < 1) || count($member_permissions_list) > count(Domain_Company_Entity_Config::MEMBER_PERMISSIONS_VALUE_LIST)) {
			throw new ParamException("invalid member permissions list");
		}

		foreach ($member_permissions_list as $k => $v) {
			Domain_Company_Entity_Config::assertValidConfigValue($k, $v);
		}

		// обновляем значение в базе
		foreach ($member_permissions_list as $k => $v) {
			Domain_Company_Entity_Config::setValue($k, $v);
		}

		// получаем обновленный конфиг
		$updated_member_permissions_list = Domain_Company_Scenario_Api::getMemberPermissions();

		// шлем ивент со всем конфигом
		Gateway_Bus_Sender::memberPermissionsUpdated($updated_member_permissions_list);
	}

	/**
	 * Получаем список ограничений участника пространства
	 */
	public static function getMemberPermissions():array {

		$member_permissions_struct_list = Domain_Company_Entity_Config::getListValue(Domain_Company_Entity_Config::MEMBER_PERMISSIONS_VALUE_LIST);

		$member_permissions_list = [];
		foreach ($member_permissions_struct_list as $k => $v) {
			$member_permissions_list[$k] = $v->value["value"];
		}

		return $member_permissions_list;
	}

	/**
	 * Получаем список уведомлений
	 */
	public static function getNotifications(int $user_id, int $user_role):array {

		// проверяем может ли пользователь выполнить это действие
		\CompassApp\Domain\Member\Entity\Member::assertUserAdministrator($user_role);

		// получаем все непрочитанные уведомления
		$unread_notification_list = Domain_Member_Entity_Menu::getAllUserUnreadNotifications($user_id);
		return self::_groupNotificationByType($unread_notification_list);
	}

	/**
	 * Раскидываем уведомления в массивы по типам
	 *
	 * @throws Domain_Member_Exception_IncorrectMenuType
	 * @long switch case
	 */
	protected static function _groupNotificationByType(array $unread_notification_list):array {

		$unread_active_member_list           = [];
		$unread_guest_member_list            = [];
		$unread_administrator_member_list    = [];
		$unread_join_request_list            = [];
		$unread_left_member_list             = [];
		$is_member_count_trial_period_unread = 0;

		// проходимся по всем уведомлениям
		foreach ($unread_notification_list as $notification) {

			match ($notification->type) {
				Domain_Member_Entity_Menu::ACTIVE_MEMBER             => $unread_active_member_list[] = $notification->action_user_id,
				Domain_Member_Entity_Menu::ADMINISTRATOR_MEMBER      => $unread_administrator_member_list[] = $notification->action_user_id,
				Domain_Member_Entity_Menu::JOIN_REQUEST              => $unread_join_request_list[] = $notification->action_user_id,
				Domain_Member_Entity_Menu::LEFT_MEMBER               => $unread_left_member_list[] = $notification->action_user_id,
				Domain_Member_Entity_Menu::MEMBER_COUNT_TRIAL_PERIOD => $is_member_count_trial_period_unread = 1,
				Domain_Member_Entity_Menu::GUEST_MEMBER              => $unread_guest_member_list[] = $notification->action_user_id,
				default                                              => throw new Domain_Member_Exception_IncorrectMenuType("not valid notification type"),
			};
		}

		// для уведомлений про найм - достаем соответствующие заявки
		$hiring_request_list      = Gateway_Db_CompanyData_HiringRequest::getByCandidateUserIdList($unread_join_request_list,
			[Domain_HiringRequest_Entity_Request::STATUS_NEED_POSTMODERATION], Gateway_Db_CompanyData_MemberMenu::NOTIFICATION_LIMIT);
		$unread_join_request_list = array_column($hiring_request_list, "hiring_request_id");

		return [
			$unread_active_member_list, $unread_guest_member_list, $unread_administrator_member_list, $unread_join_request_list,
			$unread_left_member_list, $is_member_count_trial_period_unread,
		];
	}

	/**
	 * Прочитываем уведомления по типу
	 */

	public static function readNotifications(int $user_id, int $user_role, array $type_list):void {

		// проверяем может ли пользователь выполнить это действие
		\CompassApp\Domain\Member\Entity\Member::assertUserAdministrator($user_role);

		Domain_Member_Action_ReadNotifications::do($user_id, $type_list);
	}

	/**
	 * Сценарий смены настроек ограничений сообщений в чате
	 *
	 * @throws ParseFatalException
	 * @throws ActionNotAllowed
	 * @throws cs_InvalidConfigValue
	 * @throws \queryException
	 */
	public static function setUnlimitedMessagesEditing(int $role, int $permissions, int $value):void {

		$is_edited = Domain_Company_Entity_Config::edit(
			$role, $permissions, Domain_Company_Entity_Config::UNLIMITED_MESSAGES_EDITING, $value);

		if (!$is_edited) {
			return;
		}

		// отправляем событие о переключении настройки
		Gateway_Bus_Sender::unlimitedMessagesEditingChanged($value);
	}
}
