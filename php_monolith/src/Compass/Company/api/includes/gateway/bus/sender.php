<?php

namespace Compass\Company;

use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Domain\ParseFatalException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\System\Company;

/**
 * Класс для работы с go_sender - микросервисом для общения с клиентами по websocket
 * PHP может слать запросы к go_sender указывая массив пользователей которым необходимо разослать эвенты
 * либо отправит push-уведомление если пользователя нет онлайн (и PHP попросил это сделать)
 */
class Gateway_Bus_Sender {

	protected const _TOKEN_EXPIRE_TIME = 1 * 60;   // время за которое нужно успеть авторизоваться по полученному токену

	/**
	 * послать ws событие, когда изменилось имя, цвет аватара компании, аватар компании
	 *
	 * @param string|false $name
	 * @param int|false    $avatar_color_id
	 * @param string|false $avatar_file_key
	 *
	 * @throws ParseFatalException
	 */
	public static function companyProfileChanged(string|false $name, int|false $avatar_color_id, string|false $avatar_file_key):void {

		self::_sendEventToAll([
			Gateway_Bus_Sender_Event_CompanyProfileChanged_V1::makeEvent($name, $avatar_color_id, $avatar_file_key),
		]);
	}

	/**
	 * послать ws событие о очистки аватара компании
	 *
	 * @throws ParseFatalException
	 */
	public static function companyAvatarCleared():void {

		self::_sendEventToAll([
			Gateway_Bus_Sender_Event_CompanyAvatarCleared_V1::makeEvent(),
		]);
	}

	/**
	 * послать ws событие, когда переключили значение настройки
	 *
	 * @param int $is_display_push_body
	 *
	 * @throws ParseFatalException
	 */
	public static function pushBodyDisplayChanged(int $is_display_push_body):void {

		self::_sendEventToAll([
			Gateway_Bus_Sender_Event_PushBodyDisplayChanged_V1::makeEvent($is_display_push_body),
		]);
	}

	/**
	 * послать ws событие, когда переключили значение настройки
	 *
	 * @param int $is_premium_payment_request_enabled
	 *
	 * @throws ParseFatalException
	 */
	public static function premiumPaymentRequestingChanged(int $is_premium_payment_request_enabled):void {

		self::_sendEventToAll([
			Gateway_Bus_Sender_Event_PremiumPaymentRequestingChanged_V1::makeEvent($is_premium_payment_request_enabled),
		]);
	}

	/**
	 * послать ws событие, когда создали запрос на оплату премиума пользователя
	 *
	 * @param int   $user_id
	 * @param int   $request_active_till
	 * @param int   $unread_count
	 * @param array $user_id_list
	 *
	 * @throws ParseFatalException
	 */
	public static function premiumPaymentRequestCreated(int $user_id, int $request_active_till, int $unread_count, array $user_id_list):void {

		// формируем список пользователей на отправку ws
		$talking_user_list = [];
		foreach ($user_id_list as $v) {
			$talking_user_list[] = self::makeTalkingUserItem($v, false);
		}

		self::_sendEvent([
			Gateway_Bus_Sender_Event_PremiumPaymentRequestCreated_V1::makeEvent($user_id, $request_active_till, $unread_count),
		], $talking_user_list, ws_users: [$user_id]);
	}

	/**
	 * удалён запрос на оплату
	 *
	 * @param int   $unread_count
	 * @param array $user_id_list
	 *
	 * @throws ParseFatalException
	 */
	public static function premiumPaymentRequestDeleted(int $unread_count, array $user_id_list):void {

		// формируем список пользователей на отправку ws
		$talking_user_list = [];
		foreach ($user_id_list as $v) {
			$talking_user_list[] = self::makeTalkingUserItem($v, false);
		}

		self::_sendEvent([
			Gateway_Bus_Sender_Event_PremiumPaymentRequestDeleted_V1::makeEvent($unread_count),
		], $talking_user_list);
	}

	/**
	 * послать ws-событие о прочтении запросов на оплату премиума
	 *
	 * @param array $user_list
	 *
	 * @throws ParseFatalException
	 */
	public static function premiumPaymentRequestReadAll(array $user_list):void {

		$talking_user_list = [];

		foreach ($user_list as $user_id) {
			$talking_user_list[] = self::makeTalkingUserItem($user_id, false);
		}

		self::_sendEvent([
			Gateway_Bus_Sender_Event_PremiumPaymentRequestReadAll_V1::makeEvent(),
		], $talking_user_list);
	}

	/**
	 * послать ws-событие о покупке премиума для запроса от сотрудника
	 *
	 * @param array $user_list
	 * @param int   $unread_count
	 *
	 * @throws ParseFatalException
	 */
	public static function premiumPaymentRequestPayed(array $user_list, int $unread_count):void {

		$talking_user_list = [];

		foreach ($user_list as $user_id) {
			$talking_user_list[] = self::makeTalkingUserItem($user_id, false);
		}

		self::_sendEvent([
			Gateway_Bus_Sender_Event_PremiumPaymentRequestPayed_V1::makeEvent($unread_count),
		], $talking_user_list);
	}

	/**
	 * послать ws событие, когда изменили профиль участника компании
	 *
	 * @param \CompassApp\Domain\Member\Struct\Main $user_info
	 * @param string                                $client_launch_uuid
	 *
	 * @throws ParseFatalException
	 */
	public static function memberProfileUpdated(\CompassApp\Domain\Member\Struct\Main $user_info, string $client_launch_uuid = ""):void {

		// получаем идентификатор пользователя за сеанс
		// если не передан клиентом, то генерим новый свой, чтобы не отдавать пустое значение
		if ($client_launch_uuid == "") {
			$client_launch_uuid = generateUUID();
		}

		self::_sendEventToAll([
			Gateway_Bus_Sender_Event_MemberProfileUpdated_V1::makeEvent(\CompassApp\Domain\Member\Entity\Member::formatMember($user_info), $client_launch_uuid),
		]);
	}

	/**
	 * Отправляем событие о новом уведомлении конкретному пользователю
	 *
	 * @throws ParseFatalException
	 */
	public static function memberMenuNewNotificationForUser(int $user_id, int $action_user_id, int $type, array $data = []):void {

		$talking_user_list[] = self::makeTalkingUserItem($user_id, false);

		self::_sendEvent(
			[
				Gateway_Bus_Sender_Event_MemberMenuNewNotification_V1::makeEvent($action_user_id, Domain_Member_Entity_Menu::NOTIFICATION_TYPE_SCHEMA[$type], $data),
			],
			$talking_user_list,
		);
	}

	/**
	 * Отправляем событие о новом уведомлении всем администраторам
	 *
	 * @throws ParseFatalException
	 */
	public static function memberMenuNewNotification(int $action_user_id, int $type, int $exclude_receiver_id, array $data = [], array $push_data = []):void {

		self::_sendEventToAllOwners(
			[
				Gateway_Bus_Sender_Event_MemberMenuNewNotification_V1::makeEvent($action_user_id, Domain_Member_Entity_Menu::NOTIFICATION_TYPE_SCHEMA[$type], $data),
			],
			$exclude_receiver_id,
			$type,
			$push_data
		);
	}

	/**
	 * Отправляем событие об отмене уведомления всем администраторам
	 *
	 * @throws ParseFatalException
	 */
	public static function memberMenuUndoNotification(int $action_user_id, int $type, array $data = []):void {

		self::_sendEventToAllOwners([
			Gateway_Bus_Sender_Event_MemberMenuUndoNotification_V1::makeEvent($action_user_id, Domain_Member_Entity_Menu::NOTIFICATION_TYPE_SCHEMA[$type], $data),
		]);
	}

	/**
	 * Отправляем событие о прочтении уведомлений пользователю
	 *
	 * @throws ParseFatalException
	 */
	public static function memberMenuReadNotifications(int $user_id, array $type_list):void {

		$talking_user_list[] = self::makeTalkingUserItem($user_id, false);

		self::_sendEvent([
			Gateway_Bus_Sender_Event_MemberMenuReadNotifications_V1::makeEvent($type_list),
		], $talking_user_list);
	}

	/**
	 * послать ws событие, когда переключили значение настройки карточки
	 *
	 * @param int $is_enabled
	 *
	 * @throws ParseFatalException
	 */
	public static function employeeCardSettingsChanged(int $is_enabled):void {

		self::_sendEventToAll([
			Gateway_Bus_Sender_Event_EmployeeCardSettingsChanged_V1::makeEvent($is_enabled),
		]);
	}

	/**
	 * послать ws событие, когда у пользователя изменилась роль
	 *
	 * @param int $user_id
	 * @param int $role
	 *
	 * @throws ParseFatalException
	 */
	public static function userRoleChanged(int $user_id, int $role):void {

		self::_sendEventToAll([
			Gateway_Bus_Sender_Event_UserRoleChanged_V1::makeEvent($user_id, $role),
		], [$user_id]);
	}

	/**
	 * послать ws событие, когда гостя повысили до участника
	 *
	 * @param int $user_id
	 * @param int $member_count
	 * @param int $guest_count
	 *
	 * @throws ParseFatalException
	 */
	public static function guestUpgraded(int $user_id, int $member_count, int $guest_count):void {

		self::_sendEventToAll([
			Gateway_Bus_Sender_Event_GuestUpgraded_V1::makeEvent($user_id, $member_count, $guest_count),
		], [$user_id]);
	}

	/**
	 * послать ws событие, когда пользователю разрешили нанимать увольнять
	 *
	 * @param array $user_info_list
	 *
	 * @throws ParseFatalException
	 */
	public static function userHiringRightAdd(array $user_info_list):void {

		// формируем список пользователей на отправку ws
		$talking_user_list = [];
		foreach ($user_info_list as $v) {
			$talking_user_list[] = self::makeTalkingUserItem($v->user_id, false);
		}

		self::_sendEvent([
			Gateway_Bus_Sender_Event_UserHiringRightAdd_V1::makeEvent(),
		], $talking_user_list);
	}

	/**
	 * послать ws событие, когда пользователю запретели нанимать увольнять
	 *
	 * @param int $user_id
	 *
	 * @throws ParseFatalException
	 */
	public static function userHiringRightRemove(int $user_id):void {

		// формируем список пользователей на отправку ws
		$talking_user_item = self::makeTalkingUserItem($user_id, false);

		self::_sendEvent([
			Gateway_Bus_Sender_Event_UserHiringRightRemove_V1::makeEvent(),
		], [$talking_user_item]);
	}

	/**
	 * послаем ws событие пользователю что его назначили администратором по умолчанию
	 *
	 * @param array $user_info_list
	 *
	 * @throws ParseFatalException
	 */
	public static function userForcedAdminAdd(array $user_info_list):void {

		// формируем список пользователей на отправку ws
		$talking_user_list = [];
		foreach ($user_info_list as $v) {
			$talking_user_list[] = self::makeTalkingUserItem($v->user_id, false);
		}

		self::_sendEvent([
			Gateway_Bus_Sender_Event_UserForcedAdminAdd_V1::makeEvent(),
		], $talking_user_list);
	}

	/**
	 * послаем ws событие пользователю что его убрали из администраторов по умолчанию
	 *
	 * @param int $user_id
	 *
	 * @throws ParseFatalException
	 */
	public static function userForcedAdminRemove(int $user_id):void {

		// формируем список пользователей на отправку ws
		$talking_user_item = self::makeTalkingUserItem($user_id, false);

		self::_sendEvent([
			Gateway_Bus_Sender_Event_UserForcedAdminRemove_V1::makeEvent(),
		], [$talking_user_item]);
	}

	/**
	 * отправляем ws при изменении списка ссылок
	 *
	 * @param array $user_info_list
	 * @param array $link_list
	 * @param int   $achievement_id
	 * @param int   $entity_type
	 *
	 * @throws ParseFatalException
	 */
	public static function employeeCardLinkDataChanged(array $user_info_list, array $link_list, int $achievement_id, int $entity_type):void {

		// формируем список пользователей на отправку ws
		$talking_user_list = [];
		foreach ($user_info_list as $v) {
			$talking_user_list[] = self::makeTalkingUserItem($v["user_id"], false);
		}

		self::_sendEvent([
			Gateway_Bus_Sender_Event_EmployeeCardLinkDataChanged_V1::makeEvent($link_list, $entity_type, $achievement_id),
		], $talking_user_list);
	}

	/**
	 * отправляем ws при изменении списка прав
	 *
	 * @param int $user_id
	 * @param int $role
	 * @param int $permissions
	 *
	 * @throws ParseFatalException
	 */
	public static function permissionsChanged(int $user_id, int $role, int $permissions):void {

		// формируем список пользователей на отправку ws
		$talking_user_item = self::makeTalkingUserItem($user_id, false);

		self::_sendEvent([
			Gateway_Bus_Sender_Event_PermissionsChanged_V1::makeEvent($user_id, $role, $permissions, 1),
			Gateway_Bus_Sender_Event_PermissionsChanged_V2::makeEvent($user_id, $role, $permissions, 2),
		], [$talking_user_item]);
	}

	/**
	 * отправляем ws при изменении списка прав в карточке пользователя
	 *
	 * @param int   $user_id
	 * @param array $permissions
	 *
	 * @throws ParseFatalException
	 */
	public static function profileCardPermissionsChanged(int $user_id, array $permissions):void {

		// формируем список пользователей на отправку ws
		$talking_user_item = self::makeTalkingUserItem($user_id, false);

		self::_sendEvent([
			Gateway_Bus_Sender_Event_ProfileCardPermissionsChanged_V1::makeEvent($permissions),
		], [$talking_user_item]);
	}

	/**
	 * Уведомляем клиентов, что пользователь вступил в компанию.
	 *
	 * @param int $user_id
	 * @param int $member_count
	 *
	 * @throws ParseFatalException
	 */
	public static function userJoinCompany(int $user_id, int $member_count, int $guest_count):void {

		self::_sendEventToAll([
			Gateway_Bus_Sender_Event_UserJoinCompany_V1::makeEvent($user_id, $member_count, $guest_count),
		], [$user_id]);
	}

	/**
	 * Уведомляем клиентов, что пользователь покинул компанию.
	 *
	 * @param int    $user_id
	 * @param string $reason
	 * @param int    $member_count
	 * @param string $routine_key
	 *
	 * @throws ParseFatalException
	 */
	public static function userLeftCompany(int $user_id, string $reason, int $member_count, int $guest_count, string $routine_key):void {

		self::_sendEventToAll([
			Gateway_Bus_Sender_Event_UserLeftCompany_V1::makeEvent($user_id, $reason, $member_count, $guest_count),
		], [], [], $routine_key);
	}

	/**
	 * Очистить кэш токенов для пользователя в компнаии
	 *
	 * @param int $user_id
	 *
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 */
	public static function clearUserNotificationCache(int $user_id):void {

		$request = new \SenderGrpc\ClearUserNotificationCacheRequestStruct([
			"user_id"    => $user_id,
			"company_id" => COMPANY_ID,
		]);

		$response_data = self::_doCallGrpc("ClearUserNotificationCache", $request);
		$status        = $response_data[1];

		if ($status->code !== \Grpc\STATUS_OK) {
			self::_throwIfGrpcReturnNotOk($status);
		}
	}

	/**
	 * событие на создание ссылки на найм
	 *
	 * @param array $invite_link
	 * @param array $join_link
	 * @param array $talking_user_list
	 * @param int   $creator_user_id
	 *
	 * @throws ParseFatalException
	 */
	public static function inviteLinkCreated(array $invite_link, array $join_link, array $talking_user_list, int $creator_user_id):void {

		self::_sendEvent([
			Gateway_Bus_Sender_Event_InviteLinkCreatedV2_V1::makeEvent($invite_link, $join_link),
		], $talking_user_list, ws_users: [$creator_user_id]);
	}

	/**
	 * событие на удаление инвайт ссылки
	 *
	 * @param string $link_uniq
	 * @param array  $talking_user_list
	 *
	 * @throws ParseFatalException
	 */
	public static function inviteLinkDeleted(string $link_uniq, array $talking_user_list):void {

		self::_sendEvent([
			Gateway_Bus_Sender_Event_InviteLinkDeleted_V1::makeEvent($link_uniq),
		], $talking_user_list);
	}

	/**
	 * событие на редактирования инвайт ссылки
	 *
	 * @param string $link_uniq
	 * @param array  $talking_user_list
	 *
	 * @throws ParseFatalException
	 */
	public static function inviteLinkEdited(string $link_uniq, array $talking_user_list):void {

		self::_sendEvent([
			Gateway_Bus_Sender_Event_InviteLinkEdited_V1::makeEvent($link_uniq),
		], $talking_user_list);
	}

	/**
	 * событие на изменение статуса заявки на увольнение
	 *
	 * @param array $dismissal_request
	 * @param array $talking_user_list
	 *
	 * @throws ParseFatalException
	 */
	public static function dismissalRequestChanged(array $dismissal_request, array $talking_user_list):void {

		self::_sendEvent([
			Gateway_Bus_Sender_Event_DismissalRequestChanged_V1::makeEvent($dismissal_request),
		], $talking_user_list);
	}

	/**
	 * Уведомления отключены
	 *
	 * @param int $user_id
	 * @param int $snoozed_until
	 *
	 * @throws ParseFatalException
	 */
	public static function snoozedTimerChanged(int $user_id, int $snoozed_until):void {

		$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($user_id, false);

		self::_sendEvent([
			Gateway_Bus_Sender_Event_SnoozedTimerChanged_V1::makeEvent($snoozed_until),
		], $talking_user_list);
	}

	/**
	 * уведомления отключены
	 *
	 * @param int  $user_id
	 * @param bool $is_snoozed
	 *
	 * @throws ParseFatalException
	 */
	public static function notificationsSnoozed(int $user_id, bool $is_snoozed):void {

		$talking_user_item = Gateway_Bus_Sender::makeTalkingUserItem($user_id, false);

		self::_sendEvent([
			Gateway_Bus_Sender_Event_NotificationsSnoozed_V1::makeEvent($is_snoozed),
		], [$talking_user_item]);
	}

	/**
	 * Определенный тип уведомлений временно отключен
	 *
	 * @param int $user_id
	 * @param int $event_type
	 *
	 * @throws ParseFatalException
	 */
	public static function notificationsEventSnoozed(int $user_id, int $event_type):void {

		$talking_user_item = Gateway_Bus_Sender::makeTalkingUserItem($user_id, false);

		self::_sendEvent([
			Gateway_Bus_Sender_Event_NotificationsEventSnoozed_V1::makeEvent($event_type),
		], [$talking_user_item]);
	}

	/**
	 * Определенный тип уведомлений с таймером включен
	 *
	 * @param int $user_id
	 * @param int $event_type
	 *
	 * @throws ParseFatalException
	 */
	public static function notificationsEventUnsnoozed(int $user_id, int $event_type):void {

		$talking_user_item = Gateway_Bus_Sender::makeTalkingUserItem($user_id, false);

		self::_sendEvent([
			Gateway_Bus_Sender_Event_NotificationsEventUnsnoozed_V1::makeEvent($event_type),
		], [$talking_user_item]);
	}

	/**
	 * получаем ключ ожидания для пользователя
	 *
	 * @param int $user_id
	 *
	 * @return string
	 */
	public static function getWaitRoutineKeyForUser(int $user_id):string {

		return "wait_user_" . $user_id;
	}

	/**
	 * Закрываем коннект для пользователя
	 *
	 * @param int    $user_id
	 * @param string $routine_key
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function closeConnectionsByUserIdWithWait(int $user_id, string $routine_key):void {

		$grpc_request = new \SenderGrpc\SenderCloseConnectionsByUserIdWithWaitRequestStruct([
			"user_id"     => $user_id,
			"routine_key" => $routine_key,
			"company_id"  => COMPANY_ID,
		]);

		[, $status] = self::_doCallGrpc("SenderCloseConnectionsByUserIdWithWait", $grpc_request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * Закрываем коннект для конкретного device id
	 *
	 * @param int    $user_id
	 * @param string $device_id
	 * @param string $routine_key
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function closeConnectionsByDeviceIdWithWait(int $user_id, string $device_id, string $routine_key):void {

		$grpc_request = new \SenderGrpc\SenderCloseConnectionsByDeviceIdWithWaitRequestStruct([
			"user_id"     => $user_id,
			"device_id"   => $device_id,
			"routine_key" => $routine_key,
			"company_id"  => COMPANY_ID,
		]);

		[, $status] = self::_doCallGrpc("SenderCloseConnectionsByDeviceIdWithWait", $grpc_request);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * Событие на обновление ограничений участника пространства
	 *
	 * @throws ParseFatalException
	 */
	public static function memberPermissionsUpdated(array $member_permission_list):void {

		self::_sendEventToAll([
			Gateway_Bus_Sender_Event_SpaceConfigMemberPermissionsUpdated_V1::makeEvent($member_permission_list),
		]);
	}

	/**
	 * послать ws событие, когда переключили значение настройки уведомлений главного чата
	 *
	 * @param int $is_general_chat_notification_enabled
	 *
	 * @throws ParseFatalException
	 */
	public static function generalChatNotificationSettingsChanged(int $is_general_chat_notification_enabled):void {

		self::_sendEventToAll([
			Gateway_Bus_Sender_Event_GeneralChatNotificationSettingsChanged_V1::makeEvent($is_general_chat_notification_enabled),
		]);
	}

	/**
	 * послать ws событие, когда переключили значение настройки добавлять ли пользователя в Главный чат при вступлении в пространств
	 *
	 * @param int $is_add_to_general_chat_on_hiring
	 *
	 * @throws ParseFatalException
	 */
	public static function addToGeneralChatOnHiringSettingChanged(int $is_add_to_general_chat_on_hiring):void {

		self::_sendEventToAllOwners([
			Gateway_Bus_Sender_Event_AddToGeneralChatOnHiringSettingChanged_V1::makeEvent($is_add_to_general_chat_on_hiring),
		]);
	}

	// -------------------------------------------------------
	// region Userbot
	// -------------------------------------------------------

	/**
	 * послать ws событие, когда пользователя сделали Программистом
	 *
	 * @param array $user_id_list
	 *
	 * @throws ParseFatalException
	 */
	public static function userDeveloperAdded(array $user_id_list):void {

		// формируем список пользователей на отправку ws
		$talking_user_list = [];
		foreach ($user_id_list as $v) {
			$talking_user_list[] = self::makeTalkingUserItem($v, false);
		}

		self::_sendEvent([
			Gateway_Bus_Sender_Event_UserDeveloperAdded_V1::makeEvent(),
		], $talking_user_list);
	}

	/**
	 * послать ws событие, когда пользователя убрали из Программиста
	 *
	 * @param int $user_id
	 *
	 * @throws ParseFatalException
	 */
	public static function userDeveloperRemoved(int $user_id):void {

		// формируем список пользователей на отправку ws
		$talking_user_item = self::makeTalkingUserItem($user_id, false);

		self::_sendEvent([
			Gateway_Bus_Sender_Event_UserDeveloperRemoved_V1::makeEvent(),
		], [$talking_user_item]);
	}

	/**
	 * ws-событие, когда был создан пользовательский бот
	 *
	 * @param array $userbot
	 * @param int   $userbot_as_user_id
	 * @param array $developer_user_id_list
	 *
	 * @throws ParseFatalException
	 */
	public static function userbotCreated(array $userbot, int $userbot_as_user_id, array $developer_user_id_list):void {

		// формируем список пользователей на отправку ws
		$talking_user_list = [];
		foreach ($developer_user_id_list as $v) {
			$talking_user_list[] = self::makeTalkingUserItem($v, false);
		}

		self::_sendEvent([
			Gateway_Bus_Sender_Event_UserbotCreated_V1::makeEvent($userbot),
		], $talking_user_list, ws_users: [$userbot_as_user_id]);
	}

	// endregion Userbot
	// -------------------------------------------------------

	// -------------------------------------------------------
	// region Invoice
	// -------------------------------------------------------

	/**
	 * Отправляем событие на создание счета на оплату
	 *
	 * @param int $created_by_user_id
	 *
	 * @throws ParseFatalException
	 */
	public static function invoiceCreated(int $created_by_user_id):void {

		self::_sendEventToAllOwners([
			Gateway_Bus_Sender_Event_InvoiceCreated_V1::makeEvent($created_by_user_id),
		], $created_by_user_id);
	}

	/**
	 * Отправляем событие об оплате счета
	 *
	 * @throws ParseFatalException
	 */
	public static function invoicePayed():void {

		self::_sendEventToAllOwners([
			Gateway_Bus_Sender_Event_InvoicePayed_V1::makeEvent(),
		]);
	}

	/**
	 * Отправляем событие об отмене счета
	 *
	 * @param int $invoice_id
	 *
	 * @throws ParseFatalException
	 */
	public static function invoiceCanceled(int $invoice_id):void {

		self::_sendEventToAllOwners([
			Gateway_Bus_Sender_Event_InvoiceCanceled_V1::makeEvent($invoice_id),
		]);
	}

	/**
	 * Отправляем событие об обновлении статуса пространства
	 *
	 * @throws ParseFatalException
	 */
	public static function accessStatusUpdated():void {

		self::_sendEventToAll([
			Gateway_Bus_Sender_Event_SpaceAccessStatusUpdated_V1::makeEvent(),
		]);
	}

	// endregion Invoice
	// -------------------------------------------------------

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * отправляем событие
	 *
	 * @param Struct_Sender_Event[] $event_version_list
	 * @param array                 $user_list
	 * @param array                 $push_data
	 * @param array                 $ws_users
	 *
	 * @throws ParseFatalException
	 */
	protected static function _sendEvent(array $event_version_list, array $user_list, array $push_data = [], array $ws_users = []):void {

		// проверяем что прислали корректные параметры
		self::_assertSendEventParameters($event_version_list);

		// если прислали пустой массив получателей
		if (count($user_list) < 1) {

			// ничего не делаем
			return;
		}

		// получаем название событие
		$event_name = $event_version_list[0]->event;

		// конвертируем event_version_list из структуру в массив
		$converted_event_version_list = [];
		foreach ($event_version_list as $event) {

			$converted_event_version_list[] = [
				"version" => (int) $event->version,
				"data"    => (object) $event->ws_data,
			];
		}

		self::_sendEventRequest($event_name, $user_list, $converted_event_version_list, $ws_users, $push_data);
	}

	/**
	 * Отправляем ивент всем собственникам в компании
	 *
	 * @param array $event_version_list
	 * @param int   $exclude_receiver_id
	 * @param int   $type
	 * @param array $push_data
	 *
	 * @throws ParseFatalException
	 * @long
	 */
	protected static function _sendEventToAllOwners(array $event_version_list, int $exclude_receiver_id = 0, int $type = 0, array $push_data = []):void {

		// проверяем что прислали корректные параметры
		self::_assertSendEventParameters($event_version_list);

		// получаем название событие
		$event_name = $event_version_list[0]->event;

		// конвертируем event_version_list из структуру в массив
		$converted_event_version_list = [];
		foreach ($event_version_list as $event) {

			$converted_event_version_list[] = [
				"version" => (int) $event->version,
				"data"    => (object) $event->ws_data,
			];
		}

		$owner_list = isset(Domain_Member_Entity_Menu::NOTIFICATION_PERMISSION_REQUIREMENTS[$type])

			? Domain_User_Action_Member_GetByPermissions::do([Domain_Member_Entity_Menu::NOTIFICATION_PERMISSION_REQUIREMENTS[$type]])
			: Domain_User_Action_Member_GetUserRoleList::do([Member::ROLE_ADMINISTRATOR]);

		$talking_user_list = [];

		foreach ($owner_list as $member) {

			// если нужно не слать ивент пользователю
			if ($exclude_receiver_id !== 0 && $member->user_id === $exclude_receiver_id) {
				continue;
			}

			$need_push = false;

			if ($type === Domain_Member_Entity_Menu::ACTIVE_MEMBER){
				$need_push = NEED_SEND_ACTIVE_MEMBER_PUSH === true;
			}

			if ($type === Domain_Member_Entity_Menu::JOIN_REQUEST){
				$need_push = NEED_SEND_JOIN_REQUEST_PUSH === true;
			}

			if ($type === Domain_Member_Entity_Menu::GUEST_MEMBER){
				$need_push = NEED_SEND_GUEST_MEMBER_PUSH === true;
			}

			if (isset(Domain_Member_Entity_Menu::PUSH_PERMISSION_REQUIREMENTS[$type])
				&& !Permission::hasPermission($member->permissions, Domain_Member_Entity_Menu::PUSH_PERMISSION_REQUIREMENTS[$type])) {

				$need_push = false;
			}

			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($member->user_id, $need_push);
		}

		// отправляем событие
		self::_sendEventRequest($event_name, $talking_user_list, $converted_event_version_list, push_data: $push_data);
	}

	/**
	 * Отправить событие в go_sender для всех пользователей
	 *
	 * @param array  $event_version_list
	 * @param array  $ws_user_list
	 * @param array  $push_data
	 * @param string $routine_key
	 * @param int    $is_need_push
	 *
	 * @throws ParseFatalException
	 */
	protected static function _sendEventToAll(array $event_version_list, array $ws_user_list = [], array $push_data = [], string $routine_key = "", int $is_need_push = 0):void {

		// проверяем что прислали корректные параметры
		self::_assertSendEventParameters($event_version_list);

		// получаем название событие
		$event_name = $event_version_list[0]->event;

		// конвертируем event_version_list из структуру в массив
		$converted_event_version_list = [];
		foreach ($event_version_list as $event) {

			$converted_event_version_list[] = [
				"version" => (int) $event->version,
				"data"    => (object) $event->ws_data,
			];
		}

		self::_sendEventToAllRequest($event_name, $converted_event_version_list, $ws_user_list, $push_data, $routine_key, $is_need_push);
	}

	/**
	 * проверяем параметры
	 *
	 * @param Struct_Sender_Event[] $event_version_list
	 *
	 * @throws ParseFatalException
	 */
	protected static function _assertSendEventParameters(array $event_version_list):void {

		// если прислали пустой массив версий метода
		if (count($event_version_list) < 1) {
			throw new ParseFatalException("incorrect array event version list");
		}

		// проверяем, что все версии события описывают один и тот же метод
		$ws_method_name = $event_version_list[0]->event;
		foreach ($event_version_list as $event) {

			if ($event->event !== $ws_method_name) {
				throw new ParseFatalException("different ws event names");
			}
		}
	}

	/**
	 * Отправить событие в go_sender
	 *
	 * @param string $event
	 * @param array  $user_list
	 * @param array  $event_version_list
	 * @param array  $ws_user_list
	 * @param array  $push_data
	 * @param string $routine_key
	 *
	 * @throws ParseFatalException
	 */
	protected static function _sendEventRequest(string $event, array $user_list, array $event_version_list, array $ws_user_list = [], array $push_data = [], string $routine_key = ""):void {

		// формируем параметры задачи для rabbitMq
		$params = [
			"method"             => (string) "sender.sendEvent",
			"user_list"          => (array) $user_list,
			"event"              => (string) $event,
			"event_version_list" => (array) $event_version_list,
			"push_data"          => (object) $push_data,
			"uuid"               => (string) generateUUID(),
			"ws_users"           => (array) $ws_user_list,
			"routine_key"        => (string) $routine_key,
		];

		$params = self::_prepareParams($params);

		$converted_user_list          = self::_convertReceiverUserListToGrpcStructure($user_list);
		$converted_event_version_list = self::_convertEventVersionListToGrpcStructure($params["event_version_list"]);
		$grpc_request                 = new \SenderGrpc\SenderSendEventRequestStruct([
			"user_list"          => $converted_user_list,
			"event"              => $params["event"],
			"event_version_list" => $converted_event_version_list,
			"push_data"          => toJson($params["push_data"]),
			"uuid"               => $params["uuid"],
			"ws_users"           => isset($params["ws_users"]) ? toJson($params["ws_users"]) : "",
			"company_id"         => COMPANY_ID,
		]);

		self::_sendRequestWrap("SenderSendEvent", $grpc_request, $params);
	}

	/**
	 * Отправить событие в go_sender для всех пользователей
	 *
	 * @param string $event
	 * @param array  $event_version_list
	 * @param array  $ws_user_list
	 * @param array  $push_data
	 * @param string $routine_key
	 * @param int    $is_need_push
	 *
	 * @throws ParseFatalException
	 */
	protected static function _sendEventToAllRequest(string $event, array $event_version_list, array $ws_user_list = [], array $push_data = [], string $routine_key = "", int $is_need_push = 0):void {

		// формируем параметры задачи для rabbitMq
		$params = [
			"method"             => (string) "sender.sendEventToAll",
			"event"              => (string) $event,
			"event_version_list" => (array) $event_version_list,
			"push_data"          => (object) $push_data,
			"uuid"               => (string) generateUUID(),
			"ws_users"           => (array) $ws_user_list,
			"routine_key"        => (string) $routine_key,
			"is_need_push"       => (int) $is_need_push,
		];
		$params = self::_prepareParams($params);

		$converted_event_version_list = self::_convertEventVersionListToGrpcStructure($params["event_version_list"]);
		$grpc_request                 = new \SenderGrpc\SenderSendEventToAllRequestStruct([
			"event"              => $params["event"],
			"event_version_list" => $converted_event_version_list,
			"push_data"          => toJson($params["push_data"]),
			"uuid"               => $params["uuid"],
			"ws_users"           => isset($params["ws_users"]) ? toJson($params["ws_users"]) : "",
			"is_need_push"       => $is_need_push,
			"company_id"         => COMPANY_ID,
		]);

		self::_sendRequestWrap("SenderSendEventToAll", $grpc_request, $params);
	}

	/**
	 * подготавливаем $params
	 *
	 * @param array $params
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	protected static function _prepareParams(array $params):array {

		// добавляем к параметрам задачи
		$params["ws_users"] = (object) self::makeUsers($params["ws_users"]);

		return $params;
	}

	/**
	 * конвертируем user_list в структуру понятную grpc
	 *
	 * @param array $user_list
	 *
	 * @return array
	 */
	protected static function _convertReceiverUserListToGrpcStructure(array $user_list):array {

		$output = [];
		foreach ($user_list as $user_item) {

			$output[] = new \SenderGrpc\EventUserStruct([
				"user_id"   => $user_item["user_id"],
				"need_push" => $user_item["need_push"],
			]);
		}

		return $output;
	}

	/**
	 * конвертируем event_version_list в структуру понятную grpc
	 *
	 * @param array $event_version_list
	 *
	 * @return array
	 */
	protected static function _convertEventVersionListToGrpcStructure(array $event_version_list):array {

		$output = [];
		foreach ($event_version_list as $event_version_item) {

			$output[] = new \SenderGrpc\EventVersionItem([
				"version" => (int) $event_version_item["version"],
				"data"    => toJson((object) $event_version_item["data"]),
			]);
		}

		return $output;
	}

	/**
	 * обертка для отправки запроса с возможностью переоправки через асинхронный канал при неудаче
	 *
	 * @param string $grpc_method_name
	 * @param        $grpc_request
	 * @param array  $params
	 *
	 * @throws ParseFatalException
	 * @noinspection PhpUndefinedClassInspection \Google\Protobuf\Internal\Message что ты такое?
	 */
	protected static function _sendRequestWrap(string $grpc_method_name, \Google\Protobuf\Internal\Message $grpc_request, array $params):void {

		try {

			[, $status] = self::_doCallGrpc($grpc_method_name, $grpc_request);
			if ($status->code !== \Grpc\STATUS_OK) {
				throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
			}
		} catch (Error|BusFatalException) {

			Type_System_Admin::log("go_sender", "go_sender call grpc on {$grpc_method_name}");

			// отправляем задачу в rabbitMq
			Gateway_Bus_Rabbit::sendMessageToExchange("go_sender", $params);
		}
	}

	// -------------------------------------------------------
	// WS_USERS
	// -------------------------------------------------------

	/**
	 * Формируем объект ws_users
	 *
	 * @param array $user_list
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function makeUsers(array $user_list):array {

		return [
			"user_list" => (array) $user_list,
			"signature" => (string) \CompassApp\Controller\ApiAction::getUsersSignature($user_list, time()),
		];
	}

	/**
	 * Формируем объект talking_user_item
	 *
	 * @param int  $user_id
	 * @param bool $is_need_push
	 *
	 * @return int[]
	 */
	public static function makeTalkingUserItem(int $user_id, bool $is_need_push):array {

		return [
			"user_id"   => $user_id,
			"need_push" => $is_need_push ? 1 : 0,
		];
	}

	/**
	 * Делаем токен для подключения к ws по user_id
	 *
	 * @param int    $user_id
	 * @param string $token
	 * @param string $device_id
	 * @param string $platform
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function setToken(int $user_id, string $token, string $device_id = "", string $platform = Type_Api_Platform::PLATFORM_OTHER):array {

		// формируем массив для отправки
		$request = self::_prepareSetTokenParameters($user_id, $token, $platform, $device_id);

		// получаем из конфига где находится микросервис
		[, $status] = self::_doCallGrpc("SenderSetToken", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		// если вдруг такой настройки не существует
		return [
			"token" => $token,
			"url"   => PUBLIC_WEBSOCKET_DOMINO . "?a=" . Company::getServicePostFix() . "&b=" . Company::getWsServicePort(),
		];
	}

	/**
	 * подготавливаем массив параметров для запроса
	 *
	 * @param int    $user_id
	 * @param string $new_token
	 * @param string $platform
	 * @param string $device_id
	 *
	 * @return \SenderGrpc\SenderSetTokenRequestStruct
	 */
	protected static function _prepareSetTokenParameters(int $user_id, string $new_token, string $platform, string $device_id):\SenderGrpc\SenderSetTokenRequestStruct {

		return new \SenderGrpc\SenderSetTokenRequestStruct([
			"user_id"    => $user_id,
			"token"      => $new_token,
			"platform"   => $platform,
			"device_id"  => $device_id,
			"expire"     => time() + self::_TOKEN_EXPIRE_TIME,
			"company_id" => COMPANY_ID,
		]);
	}

	/**
	 * делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @param string                            $method_name
	 * @param \Google\Protobuf\Internal\Message $request
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @noinspection PhpUndefinedClassInspection \Google\Protobuf\Internal\Message что ты такое
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		return ShardingGateway::rpc("sender", \SenderGrpc\senderClient::class)->callGrpc($method_name, $request);
	}

	/**
	 * выдаем exception, если grpc не вернул ok
	 *
	 * @param object $status
	 *
	 * @throws BusFatalException
	 * @throws ParamException
	 */
	protected static function _throwIfGrpcReturnNotOk(object $status):void {

		switch ($status->code) {

			case 400:

				$error_text = $status->details;
				throw new ParamException($error_text);

			default:
				throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

}