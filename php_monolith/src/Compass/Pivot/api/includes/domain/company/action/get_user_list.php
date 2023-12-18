<?php

namespace Compass\Pivot;

/**
 * Базовый класс для получения пользователя компании/лобби
 */
class Domain_Company_Action_GetUserList {

	/**
	 * Получаем пользователя
	 *
	 * @param int   $company_id
	 * @param array $user_id_list
	 *
	 * @return array
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \userAccessException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function do(int $company_id, array $user_id_list):array {

		$user_info_list = Gateway_Db_PivotCompany_CompanyUserList::getByUserIdList($company_id, $user_id_list, true);

		$getted_user_id_list     = [];
		$need_from_lobby_id_list = [];
		foreach ($user_id_list as $user_id) {

			if (!isset($user_info_list[$user_id])) {

				$need_from_lobby_id_list[] = $user_id;
				continue;
			}
			$getted_user_id_list[] = $user_id;
		}

		// если дан список пользователей, проверяем, действительно ли они состоят в компании
		foreach ($need_from_lobby_id_list as $user_id) {

			Gateway_Db_PivotUser_CompanyLobbyList::getOne($user_id, $company_id);
			$getted_user_id_list[] = $user_id;
		}

		return Gateway_Bus_PivotCache::getUserListInfo($getted_user_id_list);
	}
}