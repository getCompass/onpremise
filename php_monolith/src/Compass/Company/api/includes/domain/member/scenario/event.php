<?php

declare(strict_types = 1);

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Класс обработки сценариев событий.
 */
class Domain_Member_Scenario_Event {

	/**
	 * У пользователя сменился permissions в компании
	 *
	 * @param Struct_Event_Member_PermissionsChanged $event_data
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @long
	 */
	#[Type_Attribute_EventListener(Type_Event_Member_PermissionsChanged::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onMemberPermissionsChanged(Struct_Event_Member_PermissionsChanged $event_data):void {

		// если нужно удаляем все ссылки для приглашение участников
		if (Permission::canInviteMember($event_data->before_role, $event_data->before_permissions) &&
			!Permission::canInviteMember($event_data->role, $event_data->permissions)) {

			// удаляем все активные ссылки-инвайты пользователя
			Domain_User_Action_UserInviteLinkActive_DeleteAllByUser::do($event_data->user_id);
		}

		// если появились права приглашать в пространство, то добавляем в уведомления администратора заявки на вступление
		if (!Permission::canInviteMember($event_data->before_role, $event_data->before_permissions) &&
			Permission::canInviteMember($event_data->role, $event_data->permissions)) {

			$hiring_request_list = Gateway_Db_CompanyData_HiringRequest::getListByStatus(Domain_HiringRequest_Entity_Request::STATUS_NEED_POSTMODERATION, 1000, 0);

			foreach ($hiring_request_list as $hiring_request) {

				$notification_list = Domain_Member_Entity_Menu::getNotificationList(
					[$event_data->user_id], $hiring_request->candidate_user_id, Domain_Member_Entity_Menu::JOIN_REQUEST
				);

				$found_owner_id_list     = [];
				$not_found_owner_id_list = [];
				if (count($notification_list) > 0) {
					$found_owner_id_list = [$event_data->user_id];
				} else {
					$not_found_owner_id_list = [$event_data->user_id];
				}

				// пишем/обновляем уведомление в базу для администратора
				Domain_Member_Entity_Menu::createOrUpdate(
					$not_found_owner_id_list, $found_owner_id_list, $hiring_request->candidate_user_id, Domain_Member_Entity_Menu::JOIN_REQUEST
				);

				$data = ["join_request_id" => $hiring_request->hiring_request_id];

				// шлем событие новому администратору о новом заявке на вступление
				Gateway_Bus_Sender::memberMenuNewNotificationForUser(
					$event_data->user_id, $hiring_request->candidate_user_id, Domain_Member_Entity_Menu::JOIN_REQUEST, $data
				);
			}

			// обновляем бадж для администратора
			$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($event_data->user_id);
			Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, (string) $event_data->user_id, [], $extra);
		}

		// отправляем аналитику по статусу пространства, если требуется
		self::_sendSpaceAnalyticsIfNeed($event_data->role, $event_data->before_role);
	}

	/**
	 * отправляем аналитику по статусу пространства, если требуется
	 *
	 * @param int $actual_role
	 * @param int $before_role
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \cs_RowIsEmpty
	 * @throws \queryException
	 */
	protected static function _sendSpaceAnalyticsIfNeed(int $actual_role, int $before_role):void {

		$action = false;

		// если пользователь стал администратором
		if ($before_role != Member::ROLE_ADMINISTRATOR && $actual_role == Member::ROLE_ADMINISTRATOR) {
			$action = Type_Space_Analytics::NEW_ADMINISTRATOR;
		}

		// если пользователь перестал быть администратором
		if ($before_role == Member::ROLE_ADMINISTRATOR && $actual_role != Member::ROLE_ADMINISTRATOR) {
			$action = Type_Space_Analytics::DISMISS_ADMINISTRATOR;
		}

		// если пользователь покинул компанию
		if ($actual_role == Member::ROLE_LEFT) {
			$action = Type_Space_Analytics::MEMBER_COUNT_CHANGED;
		}

		if ($action === false) {
			return;
		}

		Domain_Company_Action_SendSpaceAnalytics::do($action);
	}

	/**
	 * Callback для события смены роли пользователя в пространстве
	 *
	 * @return Type_Task_Struct_Response
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \parseException
	 */
	#[Type_Attribute_EventListener(Type_Event_UserCompany_MemberRoleChanged::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	#[Type_Task_Attribute_Executor(Type_Event_UserCompany_MemberRoleChanged::EVENT_TYPE, Struct_Event_UserCompany_MemberRoleChanged::class)]
	public static function memberRoleChanged(Struct_Event_UserCompany_MemberRoleChanged $event_data):Type_Task_Struct_Response {

		// получаем актуальную роль участника
		try {
			$member_short = Domain_User_Action_Member_GetShort::do($event_data->user_id);
		} catch (\cs_RowIsEmpty) {
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
		}

		// если пользователь уже покинул пространство, то ничего не делаем
		if (Member::ROLE_LEFT === $member_short->role) {
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
		}

		// если сменилась роль с гостя и теперь пользователь полноценный участник
		if ($event_data->before_role === Member::ROLE_GUEST && in_array($member_short->role, Member::SPACE_RESIDENT_ROLE_LIST)) {

			// добавляем уведомление всем администраторам о том, что появился новый активный участник
			$company_name    = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::COMPANY_NAME)["value"];
			$avatar_color_id = \BaseFrame\Domain\User\Avatar::getColorByUserId($member_short->user_id);
			$extra           = new Domain_Member_Entity_Notification_Extra(
				0, "", $company_name, "", \BaseFrame\Domain\User\Avatar::getColorOutput($avatar_color_id)
			);
			Domain_Member_Action_AddNotification::do(
				$event_data->user_id, Domain_Member_Entity_Menu::ACTIVE_MEMBER, $extra, exclude_receiver_id: $event_data->change_role_user_id
			);

			// снимаем уведомление на меню гости для всех админов
			Domain_Member_Action_UndoNotification::do($event_data->user_id, Domain_Member_Entity_Menu::GUEST_MEMBER);
		}

		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
	}
}
