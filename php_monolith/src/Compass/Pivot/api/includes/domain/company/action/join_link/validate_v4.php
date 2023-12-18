<?php

namespace Compass\Pivot;

/**
 * Класс для валидации инвайта-ссылки
 * Обслуживает 4 версию метода /link/validate/, в которой добавилась новая обработка ошибок и поменялся их приоритет
 */
class Domain_Company_Action_JoinLink_ValidateV4 {

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

		// пробуем достать данные по ссылке
		$invite_link_rel_row = Domain_Company_Entity_JoinLink_Main::getByLink($link);

		// проверяем, является ли пользователь участником пространства, куда ведет ссылка
		$is_user_member_of_space = self::_isUserMemberOfTargetSpace($user_id, $invite_link_rel_row->company_id);

		// получаем компанию
		$company = Domain_Company_Entity_Company::get($invite_link_rel_row->company_id);

		// проверяем, что компания активна
		Domain_Company_Entity_Company::assertCompanyActive($company);

		try {

			// проверяем, что ссылка не использована
			Domain_Company_Entity_JoinLink_Main::assertJoinLinkStatusNotUsed($invite_link_rel_row);

			// проверяем, что ссылка активна
			self::_throwIfInviteLinkInactive($invite_link_rel_row);

			// делаем запрос в php_company, проверяем, что ссылку можно использовать
			/** @var Struct_Db_PivotUser_User $inviter_user_info */
			[$inviter_user_info, $entry_option, $is_postmoderation, $is_exit_status_in_progress] =
				Domain_Company_Entity_JoinLink_Main::getJoinLinkInfo($company, $user_id, $invite_link_rel_row->join_link_uniq);
		} catch (cs_JoinLinkIsUsed|cs_JoinLinkNotFound|cs_JoinLinkIsNotActive $e) {

			// если пользователь не является участником пространства, то возвращаем исключение как есть
			if (!$is_user_member_of_space) {
				throw $e;
			}

			// иначе получаем ID создателя приглашения
			$inviter_user_id = Domain_Company_Entity_JoinLink_Main::getJoinLinkCreatorUserId($company, $invite_link_rel_row->join_link_uniq);

			// выбрасываем новое исключение
			throw new cs_UserAlreadyInCompany($user_id, $invite_link_rel_row->company_id, $inviter_user_id);
		}

		// выбрасываем исключение, если пользователь является участником пространства, в которое ведет активная ссылка-приглашение
		self::_throwIfUserMemberOfSpace($invite_link_rel_row, $user_id, $is_user_member_of_space, $inviter_user_info);

		// проверяем, ожидает ли пользователь post-модерацию заявки на вступление в пространство
		$is_waiting_for_postmoderation = $user_id > 0 && self::_isUserWaitingForPostmoderation($user_id, $company);

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
	 * Проверяем, является ли пользователь участником пространства, в которую ведет ссылка-приглашение
	 *
	 * @return bool
	 */
	protected static function _isUserMemberOfTargetSpace(int $user_id, int $space_id):bool {

		try {
			Gateway_Db_PivotUser_CompanyList::getOne($user_id, $space_id);
		} catch (\cs_RowIsEmpty) {
			return false;
		}

		return true;
	}

	/**
	 * Выбрасываем исключения, если ссылка-приглашение неактивна
	 */
	protected static function _throwIfInviteLinkInactive(Struct_Db_PivotData_CompanyJoinLinkRel $invite_link_rel_row):void {

		// если ссылка-приглашение не активна, то выбрасываем исключение
		if (!Domain_Company_Entity_JoinLink_Main::isJoinLinkStatusActive($invite_link_rel_row)) {

			// выбрасываем именно это исключение, чтобы в контроллере вернуть ошибку по ТЗ
			throw new cs_JoinLinkNotFound();
		}
	}

	/**
	 * Выбрасываем исключение, если пользователь является участником пространства, в которое ведет активная ссылка-приглашение
	 */
	protected static function _throwIfUserMemberOfSpace(Struct_Db_PivotData_CompanyJoinLinkRel $invite_link_rel_row, int $user_id, bool $is_user_member_of_space, Struct_Db_PivotUser_User $inviter_user_info):void {

		// если пользователь не участник пространства, то ничего не делаем
		if (!$is_user_member_of_space) {
			return;
		}

		// иначе выбрасываем исключение
		throw new cs_UserAlreadyInCompany($user_id, $invite_link_rel_row->company_id, $inviter_user_info->user_id);
	}

	/**
	 * проверяем статус пользователя в лобби
	 *
	 * @throws cs_UserAlreadyInCompany
	 */
	protected static function _isUserWaitingForPostmoderation(int $user_id, Struct_Db_PivotCompany_Company $company):bool {

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