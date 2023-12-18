<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * Базовый класс для действия подтверждения заявки на найм нового сотрудника
 */
class Domain_HiringRequest_Action_Confirm {

	/**
	 * Выполняем action
	 *
	 * @long
	 * @throws Domain_Space_Exception_ActionRestrictedByTariff
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_HireRequestNotExist
	 * @throws cs_HiringRequestAlreadyConfirmed
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(int $approved_by_user_id, int $hiring_request_id, string $entry_role):array {

		// резолвим роль в которой новый пользователь попадет в пространство
		$role = Domain_HiringRequest_Entity_Request::resolveRoleByEntryRole($entry_role);

		// бронируем место в тарифном плане
		$is_trial_activated = self::_reserveTariffPlanSlot($approved_by_user_id, $role);

		Gateway_Db_CompanyData_HiringRequest::beginTransaction();

		try {
			$hiring_request = Gateway_Db_CompanyData_HiringRequest::getForUpdate($hiring_request_id);
		} catch (\cs_RowIsEmpty) {

			Gateway_Db_CompanyData_HiringRequest::rollback();
			throw new cs_HireRequestNotExist();
		}

		$single_item = [
			"user_id" => $hiring_request->hired_by_user_id,
			"status"  => Domain_HiringRequest_Entity_Request::HIRING_REQUEST_APPROVED_ELEMENT_STATUS,
			"order"   => 1,
		];

		// добавляем диалог с создателем в заявку
		$hiring_request->extra = Domain_HiringRequest_Entity_Request::setSingleListToCreate($hiring_request->extra, [$single_item]);

		// переводим заявку в статус «одобрено», сохраняем новый список групп по умолчанию для пользователя и ключ приглашения
		[$hiring_request, $is_need_user_add] = Domain_HiringRequest_Entity_Request::confirm($hiring_request);
		Gateway_Db_CompanyData_HiringRequest::commitTransaction();

		if ($is_need_user_add) {

			$permissions = Permission::DEFAULT;
			$entry_type  = Domain_User_Entity_Entry::getType($hiring_request->entry_id);
			$locale      = Domain_HiringRequest_Entity_Request::getLocale($hiring_request->extra);

			// получаем данные пользователя
			[$user_info, $avg_screen_time, $total_action_count, $avg_message_answer_time] =
				Gateway_Socket_Pivot::getUserInfo($hiring_request->candidate_user_id);

			// добавляем пользователя в компанию и активируем его
			$user_company_token = Domain_User_Action_AddUser::do(
				$hiring_request->candidate_user_id,
				$role,
				$permissions,
				$entry_type,
				"",
				$user_info->full_name,
				$user_info->avatar_file_key,
				$user_info->avatar_color_id,
				"",
				$locale,
				false,
				$hiring_request->hired_by_user_id,
				$hiring_request->hiring_request_id,
				approved_by_user_id: $approved_by_user_id,
				is_trial_activated: (bool) $is_trial_activated,
				avg_screen_time: $avg_screen_time,
				total_action_count: $total_action_count,
				avg_message_answer_time: $avg_message_answer_time,
			);
			Gateway_Socket_Pivot::doConfirmHiringRequest($hiring_request->candidate_user_id,
				$hiring_request->hired_by_user_id, $approved_by_user_id, $role, $permissions, $user_company_token);
		} else {

			$member_info = Gateway_Bus_CompanyCache::getMember($hiring_request->candidate_user_id);
			$user_info   = new Struct_User_Info(
				$member_info->user_id,
				$member_info->full_name,
				$member_info->avatar_file_key,
				\CompassApp\Domain\Member\Entity\Extra::getAvatarColorId($member_info->extra),
			);
		}

		// пушим ws-событие о статусе заявки для участников чата
		[$formatted_hiring_request] = Domain_HiringRequest_Action_Format::do($hiring_request, $user_info);
		$frontend_hiring_request = Apiv1_Format::hiringRequest($formatted_hiring_request);
		Domain_HiringRequest_Entity_Request::sendHiringRequestStatusChangedEvent($frontend_hiring_request);

		// пушим событие о статусе заявки в go_event
		Gateway_Event_Dispatcher::dispatch(Type_Event_HiringRequest_StatusChangedLink::create($frontend_hiring_request), true);

		// создаем диалог с создателем заявки
		Gateway_Event_Dispatcher::dispatch(Type_Event_Conversation_AddSingleList::create($hiring_request->candidate_user_id, [$hiring_request->hired_by_user_id], false, false), true);

		// убираем уведомление у всех администраторов от том, что создавалась заявка на найм
		Domain_Member_Action_UndoNotification::do($hiring_request->candidate_user_id, Domain_Member_Entity_Menu::JOIN_REQUEST, $hiring_request->hiring_request_id);

		return [$hiring_request, $user_info];
	}

	/**
	 * Резервируем слот тарифного плана для нового пользователя пространства
	 *
	 * @return bool
	 * @throws Domain_Space_Exception_ActionRestrictedByTariff
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	protected static function _reserveTariffPlanSlot(int $approved_by_user_id, int $joining_user_role):bool {

		// если пользователь вступает на роль полноценного пользователя
		if (in_array($joining_user_role, Member::SPACE_RESIDENT_ROLE_LIST)) {

			[$can_increase, $is_trial_activated] = Gateway_Socket_Pivot::increaseMemberCountLimit($approved_by_user_id);

			// если пивот сказал нельзя принять - значит запрещаем
			if (!$can_increase) {
				throw new Domain_Space_Exception_ActionRestrictedByTariff("cant confirm due tariff restrictions");
			}

			return $is_trial_activated;
		}

		// в остальных кейсах ничего не делаем
		return false;
	}
}