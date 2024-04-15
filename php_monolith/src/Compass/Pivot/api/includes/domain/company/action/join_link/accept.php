<?php

namespace Compass\Pivot;

/**
 * Класс для принятия инвайта-ссылки
 */
class Domain_Company_Action_JoinLink_Accept {

	/**
	 * Выполняем
	 *
	 * @param int    $user_id
	 * @param string $join_link_uniq
	 * @param string $comment
	 * @param string $session_uniq
	 *
	 * @return array
	 * @throws Domain_Company_Exception_IsHibernated
	 * @throws Domain_Company_Exception_IsNotServed
	 * @throws Domain_Company_Exception_IsRelocating
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws cs_ExitTaskInProgress
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_Text_IsTooLong
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @throws cs_RowDuplication
	 */
	public static function do(int $user_id, string $join_link_uniq, string $comment, string $session_uniq, bool $force_postmoderation):array {

		// получаем ссылку
		$invite_link_rel_row = self::_getJoinLink($join_link_uniq);

		// получаем компанию
		$company = Domain_Company_Entity_Company::get($invite_link_rel_row->company_id);

		// проверяем, что компания активна
		Domain_Company_Entity_Company::assertCompanyActive($company);

		// проверяем, что ссылку можно использовать
		self::_validateLink($user_id, $join_link_uniq, $company);

		// получаем информацию по пользователю
		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);

		// принимаем ссылку
		return self::_acceptLink($user_id, $join_link_uniq, $comment, $session_uniq, $company, $invite_link_rel_row, $user_info, $force_postmoderation);
	}

	/**
	 * Получаем ссылку
	 *
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 */
	protected static function _getJoinLink(string $join_link_uniq):Struct_Db_PivotData_CompanyJoinLinkRel {

		// проверяем что инвайт-ссылка валидна и существует в базе (pivot_data.company_join_link_rel)
		try {
			$join_link_rel_row = Gateway_Db_PivotData_CompanyJoinLinkRel::get($join_link_uniq);
		} catch (\cs_RowIsEmpty) {
			throw new cs_IncorrectJoinLink();
		}

		// проверяем что такая инвайт-ссылка еще не использована
		Domain_Company_Entity_JoinLink_Main::assertJoinLinkStatusNotUsed($join_link_rel_row);

		// проверяем что такая инвайт-ссылка активна
		Domain_Company_Entity_JoinLink_Main::assertJoinLinkStatusActive($join_link_rel_row);

		return $join_link_rel_row;
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
	protected static function _validateLink(int $user_id, string $join_link_uniq, Struct_Db_PivotCompany_Company $company):void {

		// делаем запрос в php_company, проверяем, что ссылку можно использовать
		[$inviter_user_info] = Domain_Company_Entity_JoinLink_Main::getJoinLinkInfo($company, $user_id, $join_link_uniq);

		try {

			// проверяем, находится ли пользователь в предбаннике
			self::_checkUserLobbyStatus($user_id, $company);
		} catch (cs_UserAlreadyInCompany $e) {

			$e->setCompanyId($company->company_id);
			$e->setInviterUserId($inviter_user_info->user_id);
			throw $e;
		}
	}

	/**
	 * проверяем статус пользователя в лобби
	 *
	 * @throws cs_UserAlreadyInCompany
	 */
	protected static function _checkUserLobbyStatus(int $user_id, Struct_Db_PivotCompany_Company $company):void {

		// проверяем, что приглашенный не находится в компании, куда ведет ссылка
		Domain_Company_Entity_User_Member::assertUserIsNotMemberOfCompany($user_id, $company->company_id);

		// проверяем, находится ли пользователь в предбаннике
		try {

			$user_lobby = Domain_Company_Entity_User_Lobby::get($user_id, $company->company_id);

			// проверяем статус пользователя в предбаннике
			Domain_Company_Entity_User_Lobby::assertUserNotPostModeration($user_lobby->status);
			Domain_Company_Entity_User_Lobby::assertUserFiredOrRevoked($user_lobby->status);
		} catch (cs_UserAlreadyInPostModeration) {

			throw new cs_UserAlreadyInCompany();
		} catch (\cs_RowIsEmpty) {
			// если нет в предбаннике, значит всё оки
		}
	}

	/**
	 * Принимаем ссылку
	 *
	 * @long - try ... catch
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_ExitTaskInProgress
	 * @throws cs_JoinLinkIsNotActive
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_Text_IsTooLong
	 * @throws \parseException
	 * @throws \queryException
	 */
	protected static function _acceptLink(
		int                                    $user_id,
		string                                 $join_link_uniq,
		string                                 $comment,
		string                                 $session_uniq,
		Struct_Db_PivotCompany_Company         $company,
		Struct_Db_PivotData_CompanyJoinLinkRel $invite_link_rel_row,
		Struct_Db_PivotUser_User               $user_info,
		bool                                   $force_postmoderation
	):array {

		$company_id = $invite_link_rel_row->company_id;

		try {

			// получаем связь пользователь-ссылка
			$user_join_link_rel = Domain_Company_Entity_JoinLink_UserRel::getByInviteLink($join_link_uniq, $user_id, $company_id);
			Domain_Company_Entity_JoinLink_UserRel::throwIfInviteAlreadyUsed($user_join_link_rel);
		} catch (\cs_RowIsEmpty) {
			$user_join_link_rel = false;
		}

		[$response, $user_info] = Domain_Company_Action_JoinLink_Join::run(
			$user_info,
			$invite_link_rel_row,
			$company,
			$user_join_link_rel,
			$session_uniq,
			$comment,
			$force_postmoderation
		);
		return [$company_id, $company, $response, $user_info];
	}
}