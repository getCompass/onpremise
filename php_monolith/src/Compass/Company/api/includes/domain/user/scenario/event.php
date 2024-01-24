<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Агрегатор подписок на событие для домена пользователь.
 */
class Domain_User_Scenario_Event {

	private const _QUERY_LIMIT = 1000;

	/**
	 * Callback для события присоединения сотрудника в компанию
	 *
	 * @param Struct_Event_UserCompany_UserJoinedCompany $event_data
	 *
	 * @return Type_Task_Struct_Response
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	#[Type_Attribute_EventListener(Type_Event_UserCompany_UserJoinedCompany::EVENT_TYPE, Type_Attribute_EventListener::TASK_QUEUE)]
	#[Type_Task_Attribute_Executor(Type_Event_UserCompany_UserJoinedCompany::EVENT_TYPE, Struct_Event_UserCompany_UserJoinedCompany::class)]
	public static function onUserJoinedCompany(Struct_Event_UserCompany_UserJoinedCompany $event_data):Type_Task_Struct_Response {

		$member = Gateway_Db_CompanyData_MemberList::getOne($event_data->user_id);

		// если выясняется что пользователь уже покинул пространство
		if ($member->role === \CompassApp\Domain\Member\Entity\Member::ROLE_LEFT) {
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
		}

		// получаем число пользователей/гостей в компании
		$member_count = Domain_User_Action_Config_GetMemberCount::do();
		$guest_count  = Domain_User_Action_Config_GetGuestCount::do();

		// шлем ws о вступлении пользователя в компанию
		Gateway_Bus_Sender::userJoinCompany($event_data->user_id, $member_count, $guest_count);

		// если в пространство вступил новый полноценный участник
		$company_name    = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::COMPANY_NAME)["value"];
		$avatar_color_id = \BaseFrame\Domain\User\Avatar::getColorByUserId($member->user_id);
		$extra           = new Domain_Member_Entity_Notification_Extra(
			0, $member->full_name, $company_name, $member->avatar_file_key, \BaseFrame\Domain\User\Avatar::getColorOutput($avatar_color_id)
		);
		if ($member->role === \CompassApp\Domain\Member\Entity\Member::ROLE_MEMBER) {

			// добавляем уведомление всем администраторам о том, что появился новый пользователь
			Domain_Member_Action_AddNotification::do(
				$event_data->user_id, Domain_Member_Entity_Menu::ACTIVE_MEMBER, $extra, exclude_receiver_id: $event_data->approved_by_user_id
			);
		}

		// если в пространство вступил гость
		if ($member->role === \CompassApp\Domain\Member\Entity\Member::ROLE_GUEST) {

			// добавляем уведомление всем администраторам о том, что появился новый гость
			Domain_Member_Action_AddNotification::do(
				$event_data->user_id, Domain_Member_Entity_Menu::GUEST_MEMBER, $extra, exclude_receiver_id: $event_data->approved_by_user_id
			);
		}

		// убираем уведомление у всех администраторов от том, что был удален пользователь (редкий кейс если уволенный пользователь вступил обратно)
		if ($event_data->is_user_already_was_in_company) {
			Domain_Member_Action_UndoNotification::do($event_data->user_id, Domain_Member_Entity_Menu::LEFT_MEMBER);
		}

		// отправляем аналитику по статусу пространства
		Domain_Company_Action_SendSpaceAnalytics::do(Type_Space_Analytics::MEMBER_COUNT_CHANGED);

		// отмечаем в intercom, что пользователь вступил в пространство
		Gateway_Socket_Intercom::userJoined($event_data->user_id, $member_count);
		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
	}

	/**
	 * Callback для события не нужно ли запустить задачи наблюдателя пользователей
	 *
	 * @param Struct_Event_User_ObserverCheckRequired $event_data
	 *
	 * @return Type_Task_Struct_Response
	 * @throws \parseException
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	#[Type_Task_Attribute_Executor(Type_Event_User_ObserverCheckRequired::EVENT_TYPE, Struct_Event_User_ObserverCheckRequired::class)]
	public static function onObserverCheckRequired(Struct_Event_User_ObserverCheckRequired $event_data):Type_Task_Struct_Response {

		// формируем данные
		$limit                    = self::_QUERY_LIMIT;
		$iteration                = 0;
		$observer_data_by_user_id = [];

		do {

			$list = Gateway_Db_CompanySystem_ObserverMember::getAll($limit, $iteration++ * $limit);

			foreach ($list as $row) {
				$observer_data_by_user_id[(int) $row["user_id"]] = $row["data"];
			}
		} while (count($list));

		// разбиваем полученные задачи на чанки
		$chunk_observer_data_by_user_id = array_chunk($observer_data_by_user_id, 50, true);

		$closest_work_at_list = [time() + 60 * 10];

		// для полученных задач отправляем ивент для начала процесса наблюдения за пользователями
		foreach ($chunk_observer_data_by_user_id as $observer_data_by_user_id) {

			$user_job_list_batch = [];

			foreach ($observer_data_by_user_id as $user_id => $row_data) {
				$user_job_list_batch[] = ["user_id" => $user_id, "data" => $row_data];
			}

			$closest_work_at_list[] = Type_User_Observer::doWork($user_job_list_batch);
		}

		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, min($closest_work_at_list));
	}

	/**
	 * обновление информации списка пользователей
	 *
	 * @param Struct_Event_UserCompany_UpdateUserInfoList $event_data
	 */
	#[Type_Attribute_EventListener(Type_Event_UserCompany_UpdateUserInfoList::EVENT_TYPE)]
	public static function updateUserInfoList(Struct_Event_UserCompany_UpdateUserInfoList $event_data):Type_Task_Struct_Response {

		// обновляем профили пользователей
		foreach ($event_data->user_info_update_list as $user_id => $user_info) {

			// формируем массив на обновление
			$updated = [
				"updated_at"      => time(),
				"full_name"       => $user_info["full_name"],
				"avatar_file_key" => $user_info["avatar_file_key"],
			];

			Gateway_Db_CompanyData_MemberList::set($user_id, $updated);
		}

		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
	}
}
