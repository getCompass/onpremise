<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * Класс для работы с сущностью ссылки.
 */
class Domain_Link_Entity_Link {

	/**
	 * Выполняет валидацию ссылки перед регистрацией пользователя.
	 * Для случаев, когда регистрация невозможна, если нет приглашения.
	 *
	 * @throws Domain_Link_Exception_TemporaryUnavailable
	 * @throws \busException
	 * @throws \userAccessException
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_JoinLinkNotFound
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserNotFound
	 */
	public static function validateBeforeRegistration(Struct_Db_PivotData_CompanyJoinLinkRel $invite_link_rel_row):Struct_Link_ValidationResult {

		try {

			// проверяем, что компания вообще существует и получаем ее данные
			$company = Domain_Company_Entity_Company::get($invite_link_rel_row->company_id);
		} catch (cs_CompanyNotExist|cs_CompanyIncorrectCompanyId) {
			throw new ParamException("passed bad link");
		}

		try {

			// убеждаемся, что компания активна
			// и по приглашению можно сразу вступить можно
			Domain_Company_Entity_Company::assertCompanyActive($company);
		} catch (Domain_Company_Exception_IsHibernated|Domain_Company_Exception_IsRelocating) {
			throw new Domain_Link_Exception_TemporaryUnavailable("link is not available at this moment");
		} catch (Domain_Company_Exception_IsNotServed) {
			throw new cs_IncorrectJoinLink("passed bad link");
		}

		// проверяем, что ссылка не использована и активна
		Domain_Company_Entity_JoinLink_Main::assertJoinLinkStatusNotUsed($invite_link_rel_row);
		static::assertIsActive($invite_link_rel_row);

		// делаем запрос в php_company, проверяем, что ссылку можно использовать
		$answer = Domain_Company_Entity_JoinLink_Main::getJoinLinkInfo($company, 0, $invite_link_rel_row->join_link_uniq);
		[$inviter_user_info, $entry_option, $is_postmoderation, $was_member] = $answer;

		return new Struct_Link_ValidationResult(
			$invite_link_rel_row, false, $company, $inviter_user_info, $entry_option,
			$is_postmoderation, false, false, $was_member
		);
	}

	/**
	 * Выполняет валидацию ссылки для существующего пользователя.
	 * Стандартный сценарий проверки для уже зарегистрированных пользователей.
	 *
	 * @throws Domain_Link_Exception_TemporaryUnavailable
	 * @throws \busException
	 * @throws \userAccessException
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_JoinLinkNotFound
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserNotFound
	 * @long
	 */
	public static function validateForUser(int $user_id, Struct_Db_PivotData_CompanyJoinLinkRel $invite_link_rel_row):Struct_Link_ValidationResult {

		try {

			// проверяем, что компания вообще существует и получаем ее данные
			$company = Domain_Company_Entity_Company::get($invite_link_rel_row->company_id);
		} catch (cs_CompanyNotExist|cs_CompanyIncorrectCompanyId) {
			throw new ParamException("passed bad link");
		}

		try {

			// убеждаемся, что компания активна
			// и по приглашению можно сразу вступить можно
			Domain_Company_Entity_Company::assertCompanyActive($company);
		} catch (Domain_Company_Exception_IsHibernated|Domain_Company_Exception_IsRelocating) {
			throw new Domain_Link_Exception_TemporaryUnavailable("link is not available at this moment");
		} catch (Domain_Company_Exception_IsNotServed) {
			throw new cs_IncorrectJoinLink("passed bad link");
		}

		try {

			// проверяем принимал ли пользователь ранее инвайт
			$user_join_link_rel = static::assertWasAcceptedByUser($invite_link_rel_row, $user_id);

			// проверяем, что ссылка не использована и активна
			Domain_Company_Entity_JoinLink_Main::assertJoinLinkStatusNotUsed($invite_link_rel_row);
			static::assertIsActive($invite_link_rel_row);

			// делаем запрос в php_company, проверяем, что ссылку можно использовать
			$answer = Domain_Company_Entity_JoinLink_Main::getJoinLinkInfo($company, $user_id, $invite_link_rel_row->join_link_uniq);
			[$inviter_user_info, $entry_option, $is_postmoderation, $is_exit_status_in_progress, $was_member] = $answer;

			// если пользователь ещё не завершил увольнение - кидаем ошибку
			if ($is_exit_status_in_progress == 1) {
				throw new cs_ExitTaskInProgress("user has not finished exit the company yet");
			}
		} catch (cs_JoinLinkIsUsed|cs_JoinLinkNotFound|cs_JoinLinkIsNotActive $e) {

			// если пользователь является участником пространства, то говорим, что пользователь уже участник
			if (Domain_Company_Entity_User_Member::isMember($user_id, $invite_link_rel_row->company_id)) {

				/** @var Struct_Db_PivotUser_User $inviter_user_info */
				$answer = Domain_Company_Entity_JoinLink_Main::getJoinLinkInfo($company, $user_id, $invite_link_rel_row->join_link_uniq);
				[$inviter_user_info, $entry_option, $is_postmoderation, $is_exit_status_in_progress, $was_member] = $answer;

				throw new cs_UserAlreadyInCompany(
					$user_id, $invite_link_rel_row->company_id, $inviter_user_info->user_id, $inviter_user_info->full_name, $is_postmoderation, $entry_option, $was_member
				);
			}

			// ссылка неактивна, возвращаем ошибку как есть
			throw $e;
		}

		// проверяем, ожидает ли пользователь post-модерацию заявки на вступление в пространство
		$is_waiting_for_postmoderation = Domain_Company_Entity_User_Member::isWaitingForPostmoderation($user_id, $company->company_id);

		// выбрасываем исключение, если пользователь является участником пространства, в которое ведет активная ссылка-приглашение,
		//  или если пользователь находится на постмодерации
		// если пользователь не участник пространства, то ничего не делаем
		if (Domain_Company_Entity_User_Member::isMember($user_id, $invite_link_rel_row->company_id) || $is_waiting_for_postmoderation) {

			/** @var Struct_Db_PivotUser_User $inviter_user_info */
			$answer = Domain_Company_Entity_JoinLink_Main::getJoinLinkInfo($company, $user_id, $invite_link_rel_row->join_link_uniq);
			[$inviter_user_info, $entry_option, $is_postmoderation, $is_exit_status_in_progress, $was_member] = $answer;

			// если на пивоте есть информация, что пользователь ожидает одобрения вступления
			$is_postmoderation |= $is_waiting_for_postmoderation;

			throw new cs_UserAlreadyInCompany(
				$user_id, $invite_link_rel_row->company_id, $inviter_user_info->user_id, $inviter_user_info->full_name, $is_postmoderation, $entry_option, $was_member
			);
		}

		return new Struct_Link_ValidationResult(
			$invite_link_rel_row, $user_join_link_rel, $company, $inviter_user_info, $entry_option,
			$is_postmoderation, $is_waiting_for_postmoderation, $is_exit_status_in_progress, $was_member
		);
	}

	/**
	 * Проверяет, использовал ли пользователь это приглашение ранее.
	 * @throws cs_JoinLinkIsUsed
	 */
	public static function assertWasAcceptedByUser(Struct_Db_PivotData_CompanyJoinLinkRel $invite_link_rel_row, int $user_id):Struct_Db_PivotData_CompanyJoinLinkUserRel|false {

		try {
			$invite_link_row = Domain_Company_Entity_JoinLink_UserRel::getByInviteLink($invite_link_rel_row->join_link_uniq, $user_id, $invite_link_rel_row->company_id);
		} catch (\cs_RowIsEmpty) {
			return false;
		}

		try {
			Domain_Company_Entity_JoinLink_UserRel::throwIfInviteAlreadyUsed($invite_link_row);
		} catch (cs_JoinLinkIsNotActive) {
			throw new cs_JoinLinkIsUsed("link already used by user");
		}

		return $invite_link_row;
	}

	/**
	 * Выбрасываем исключения, если ссылка-приглашение неактивна.
	 * @throws cs_JoinLinkNotFound
	 */
	public static function assertIsActive(Struct_Db_PivotData_CompanyJoinLinkRel $invite_link_rel_row):void {

		if (Domain_Company_Entity_JoinLink_Main::isJoinLinkStatusActive($invite_link_rel_row)) {
			return;
		}

		throw new cs_JoinLinkNotFound();
	}

	/**
	 * получаем данные ссылки приглашения для участника компании
	 */
	public static function getJoinLinkInfoForMember(int $user_id, Struct_Db_PivotData_CompanyJoinLinkRel $invite_link_rel_row):Struct_Link_ValidationResult {

		try {

			// проверяем, что компания вообще существует и получаем ее данные
			$company = Domain_Company_Entity_Company::get($invite_link_rel_row->company_id);
		} catch (cs_CompanyNotExist|cs_CompanyIncorrectCompanyId) {
			throw new ParamException("passed bad link");
		}

		try {

			// убеждаемся, что компания вообще в адеквате
			// и по приглашению можно сразу вступить можно
			Domain_Company_Entity_Company::assertCompanyActive($company);
		} catch (Domain_Company_Exception_IsHibernated|Domain_Company_Exception_IsRelocating) {
			throw new Domain_Link_Exception_TemporaryUnavailable("link is not available at this moment");
		} catch (Domain_Company_Exception_IsNotServed) {
			throw new cs_IncorrectJoinLink("passed bad link");
		}

		$user_join_link_rel = Domain_Company_Entity_JoinLink_UserRel::getByInviteLink($invite_link_rel_row->join_link_uniq, $user_id, $invite_link_rel_row->company_id);

		// проверяем, что пользователь является участником пространства
		if (!Domain_Company_Entity_User_Member::isMember($user_id, $invite_link_rel_row->company_id)) {
			throw new cs_UserNotInCompany("user is not member of company");
		}

		/** @var Struct_Db_PivotUser_User $inviter_user_info */
		$answer = Domain_Company_Entity_JoinLink_Main::getJoinLinkInfoForMember($company, $user_id, $invite_link_rel_row->join_link_uniq);
		[$inviter_user_info, $entry_option, $is_postmoderation, $is_exit_status_in_progress, $was_member] = $answer;

		// проверяем, ожидает ли пользователь post-модерацию заявки на вступление в пространство
		$is_waiting_for_postmoderation = Domain_Company_Entity_User_Member::isWaitingForPostmoderation($user_id, $company->company_id);

		return new Struct_Link_ValidationResult(
			$invite_link_rel_row, $user_join_link_rel, $company, $inviter_user_info, $entry_option,
			$is_postmoderation, $is_waiting_for_postmoderation, $is_exit_status_in_progress, $was_member
		);
	}
}