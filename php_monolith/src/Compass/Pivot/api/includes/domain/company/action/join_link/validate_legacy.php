<?php

namespace Compass\Pivot;

/**
 * Класс для валидации инвайта-ссылки
 * Старая реализация, для поддержки старых клиентов
 */
class Domain_Company_Action_JoinLink_ValidateLegacy {

	/**
	 * Выполняем
	 *
	 * @param int    $user_id
	 * @param string $link
	 *
	 * @return array
	 * @throws Domain_Company_Exception_IsHibernated
	 * @throws Domain_Company_Exception_IsRelocating
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_JoinLinkNotFound
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public static function do(int $user_id, string $link):array {

		// получаем ссылку
		$invite_link_rel_row = self::_getInviteLink($link);

		// получаем компанию
		$company = Domain_Company_Entity_Company::get($invite_link_rel_row->company_id);

		// проверяем, что компания активна
		Domain_Company_Entity_Company::assertCompanyActive($company);

		// проверяем, что ссылку можно использовать
		return self::_validateLink($user_id, $invite_link_rel_row, $company);
	}

	/**
	 * Получаем ссылку
	 *
	 * @param string $link
	 *
	 * @return Struct_Db_PivotData_CompanyJoinLinkRel
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_JoinLinkNotFound
	 */
	protected static function _getInviteLink(string $link):Struct_Db_PivotData_CompanyJoinLinkRel {

		// пробуем достать данные по ссылке
		$invite_link_rel_row = Domain_Company_Entity_JoinLink_Main::getByLink($link);

		// проверяем, что ссылка не использована
		Domain_Company_Entity_JoinLink_Main::assertJoinLinkStatusNotUsed($invite_link_rel_row);

		// проверяем, что ссылка активна
		Domain_Company_Entity_JoinLink_Main::assertJoinLinkStatusActive($invite_link_rel_row);

		return $invite_link_rel_row;
	}

	/**
	 * Проверяем, что ссылку можно использовать
	 *
	 * @throws \busException
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	protected static function _validateLink(int $user_id, Struct_Db_PivotData_CompanyJoinLinkRel $invite_link_rel_row, Struct_Db_PivotCompany_Company $company):array {

		// делаем запрос в php_company, проверяем, что ссылку можно использовать
		[$inviter_user_info, $entry_option, $is_postmoderation, $is_exit_status_in_progress] =
			Domain_Company_Entity_JoinLink_Main::getJoinLinkInfo($company, $user_id, $invite_link_rel_row->join_link_uniq);

		// если необходимо получить информацию по пользователю
		$is_waiting_for_postmoderation = false;
		if ($user_id > 0) {

			try {

				// получаем статус пользователя на пост модерации
				$is_waiting_for_postmoderation = self::_isUserWaitingForPostmoderation($user_id, $company);
			} catch (cs_UserAlreadyInCompany $e) {

				$e->setCompanyId($company->company_id);
				$e->setInviterUserId($inviter_user_info->user_id);
				throw $e;
			}
		}

		// проверяем принимал ли пользователь ранее инвайт
		try {

			$invite_link_row = Domain_Company_Entity_JoinLink_UserRel::getByInviteLink($invite_link_rel_row->join_link_uniq, $user_id, $company->company_id);
			Domain_Company_Entity_JoinLink_UserRel::throwIfInviteAlreadyUsed($invite_link_row);
		} catch (\cs_RowIsEmpty) {
			// это нормально - продолжаем
		}

		return [$invite_link_rel_row, $company, $inviter_user_info, $entry_option, $is_postmoderation, (int) $is_waiting_for_postmoderation, $is_exit_status_in_progress];
	}

	/**
	 * проверяем статус пользователя в лобби
	 *
	 * @throws cs_UserAlreadyInCompany
	 */
	protected static function _isUserWaitingForPostmoderation(int $user_id, Struct_Db_PivotCompany_Company $company):bool {

		// проверяем, что приглашенный не находится в компании, куда ведет ссылка
		Domain_Company_Entity_User_Member::assertUserIsNotMemberOfCompany($user_id, $company->company_id);

		// проверяем, находится ли пользователь в предбаннике
		try {

			$user_lobby = Domain_Company_Entity_User_Lobby::get($user_id, $company->company_id);

			// проверяем статус пользователя в предбаннике
			Domain_Company_Entity_User_Lobby::assertUserNotPostModeration($user_lobby->status);
			Domain_Company_Entity_User_Lobby::assertUserFiredOrRevoked($user_lobby->status);
		} catch (cs_UserAlreadyInPostModeration) {

			return true;
		} catch (\cs_RowIsEmpty) {
			// если нет в предбаннике, значит всё оки
		}

		return false;
	}
}