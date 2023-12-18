<?php

namespace Compass\Company;

/**
 * Сценарии заявки для API
 */
class Domain_HiringRequest_Scenario_Socket {

	/**
	 * Получаем одну заявку
	 *
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_HireRequestNotExist
	 * @throws cs_IncorrectHiringRequestId
	 * @throws \cs_RowIsEmpty
	 * @throws cs_UserHasNoRightsToHiring
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function get(int $user_id, int $hiring_request_id, bool $is_from_script = false):array {

		// проверяем, параметры с запроса
		Domain_HiringRequest_Entity_Validator::assertHiringRequestId($hiring_request_id);

		// получаем заявку
		$hiring_request = Domain_HiringRequest_Entity_Request::get($hiring_request_id);
		$user_info      = Domain_HiringRequest_Action_GetUserInfoFromRequest::do($hiring_request);

		// проверяем, доступы у пользователя
		if (!$is_from_script) {
			Domain_HiringRequest_Entity_Permission::checkRequestAllowedForUser($user_id, $hiring_request);
		}

		return Domain_HiringRequest_Action_Format::do($hiring_request, $user_info);
	}

	/**
	 * получаем доступные для пользователя сущности заявок найма
	 *
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	public static function getAllowedList(int $user_id, array $request_id_list):array {

		// достаем заявки из базы
		$hiring_request_list = Domain_HiringRequest_Entity_Request::getList($request_id_list, count($request_id_list));

		if (count($hiring_request_list) < 1) {
			return [[], $request_id_list];
		}

		// если какие-то заявки не найдены, то добавляем айди таких заявок в список недоступных
		$found_hiring_id_list       = array_column((array) $hiring_request_list, "hiring_request_id");
		$not_allowed_hiring_id_list = array_diff($request_id_list, $found_hiring_id_list);

		// проверяем имеет ли пользователь доступ к найму и увольнению
		$user = Domain_User_Action_Member_GetShort::do($user_id);
		try {
			\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($user->role, $user->permissions);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			[$hiring_request_list, $not_allowed_hiring_id_list] = self::_getAllowedList($user_id, $hiring_request_list, $not_allowed_hiring_id_list);
		}

		$user_info_list = [];
		foreach ($hiring_request_list as $hiring_request) {
			$user_info_list[$hiring_request->candidate_user_id] = Domain_HiringRequest_Action_GetUserInfoFromRequest::do($hiring_request);
		}

		$formatted_hiring_request_list = [];
		foreach ($hiring_request_list as $hiring_request) {

			$user_info = false;
			if (isset($user_info_list[$hiring_request->candidate_user_id])) {
				$user_info = $user_info_list[$hiring_request->candidate_user_id];
			}
			[$formatted_hiring_request] = Domain_HiringRequest_Action_Format::do($hiring_request, $user_info);
			$formatted_hiring_request_list[] = $formatted_hiring_request;
		}
		return [$formatted_hiring_request_list, array_unique($not_allowed_hiring_id_list)];
	}

	// проверяем для обычного сотрудника доступность заявок для найма
	protected static function _getAllowedList(int $user_id, array $hiring_request_list, array $not_allowed_hiring_id_list):array {

		// (собираем список заявок, где он создатель, параллельно собрав список айди недоступных для него)
		$hiring_request_list = array_filter($hiring_request_list, function(Struct_Db_CompanyData_HiringRequest $hiring_request)
		use ($user_id, &$not_allowed_hiring_id_list) {

			if ($hiring_request->hired_by_user_id == $user_id) {
				return true;
			}

			$not_allowed_hiring_id_list[] = $hiring_request->hiring_request_id;
			return false;
		});

		return [$hiring_request_list, $not_allowed_hiring_id_list];
	}

	/**
	 * Отклоняем заявку на найм по entry_id
	 */
	public static function revoke(int $entry_id, string $candidate_full_name, string $candidate_avatar_file_key, int $candidate_avatar_color_id):void {

		// получаем заявку
		$hiring_request = Domain_HiringRequest_Entity_Request::getByEntryId($entry_id);

		if ($hiring_request->status != Domain_HiringRequest_Entity_Request::STATUS_NEED_POSTMODERATION) {
			throw new cs_HiringRequestNotPostmoderation();
		}
		Domain_HiringRequest_Action_Revoke::do($hiring_request, $candidate_full_name, $candidate_avatar_file_key, $candidate_avatar_color_id);
	}
}
