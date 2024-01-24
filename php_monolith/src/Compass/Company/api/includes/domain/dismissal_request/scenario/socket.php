<?php

namespace Compass\Company;

/**
 * Сценарии заявки для API
 */
class Domain_DismissalRequest_Scenario_Socket {

	/**
	 * Получаем одну заявку
	 *
	 * @throws \busException
	 * @throws cs_DismissalRequestNotExist
	 * @throws cs_IncorrectDismissalRequestId
	 * @throws cs_UserHasNotRightsToDismiss
	 * @throws \cs_UserIsNotMember
	 */
	public static function get(int $user_id, int $dismissal_request_id, bool $is_from_script = false):Struct_Db_CompanyData_DismissalRequest {

		// проверяем, параметры с запроса
		Domain_DismissalRequest_Entity_Validator::assertDismissalRequestId($dismissal_request_id);

		// получаем заявку
		$dismissal_request = Domain_DismissalRequest_Entity_Request::get($dismissal_request_id);

		// проверяем, доступы у пользователя
		if (!$is_from_script) {
			Domain_DismissalRequest_Entity_Permission::checkRequestAllowedForUser($user_id, $dismissal_request);
		}

		return $dismissal_request;
	}

	/**
	 * получаем доступные для пользователя сущности заявок увольнения
	 *
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 */
	public static function getAllowedList(int $user_id, array $request_id_list):array {

		// достаем заявки из базы
		$dismissal_request_list = Domain_DismissalRequest_Entity_Request::getList($request_id_list, count($request_id_list));

		if (count($dismissal_request_list) < 1) {
			return [[], []];
		}

		// если какие-то заявки не найдены, то добавляем айди таких заявок в список недоступных
		$found_dismissal_id_list       = array_column((array) $dismissal_request_list, "dismissal_request_id");
		$not_allowed_dismissal_id_list = array_diff($request_id_list, $found_dismissal_id_list);

		// проверяем имеет ли пользователь доступ к найму и увольнению
		$user = Domain_User_Action_Member_GetShort::do($user_id);
		try {
			\CompassApp\Domain\Member\Entity\Permission::canKickMember($user->role, $user->permissions);
		} catch (cs_CompanyUserIsEmployee) {
			[$dismissal_request_list, $not_allowed_dismissal_id_list] = self::_getAllowedList($user_id, $dismissal_request_list, $not_allowed_dismissal_id_list);
		}

		return [$dismissal_request_list, array_unique($not_allowed_dismissal_id_list)];
	}

	// проверяем для обычного сотрудника доступность заявок для увольнения
	protected static function _getAllowedList(int $user_id, array $dismissal_request_list, array $not_allowed_dismissal_id_list):array {

		// (собираем список заявок, где он создатель, параллельно собрав список айди недоступных для него)
		$dismissal_request_list = array_filter($dismissal_request_list, function(Struct_Db_CompanyData_DismissalRequest $dismissal_request)
		use ($user_id, &$not_allowed_dismissal_id_list) {

			if ($dismissal_request->creator_user_id == $user_id) {
				return true;
			}

			$not_allowed_dismissal_id_list[] = $dismissal_request->dismissal_request_id;
			return false;
		});

		return [$dismissal_request_list, $not_allowed_dismissal_id_list];
	}
}
