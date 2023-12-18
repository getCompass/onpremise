<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Сценарии ссылок для API
 */
class Domain_Link_Scenario_Api {

	/**
	 * Метод для запроса инвайта по ссылке
	 *
	 * @param int    $user_id
	 * @param string $link
	 * @param string $session_uniq
	 * @param string $method_version
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
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public static function validateLink(int $user_id, string $link, string $session_uniq, int $method_version):array {

		// парсим что это за ссылка
		[$action, $parsed_link, $invite_code] = Domain_Link_Action_Parse::do($link);

		// валидируем в зависимости от типа приглашения
		$action_data = match ($action) {

			"join_link" => self::validateJoinLink($user_id, $parsed_link, $session_uniq),

			// кидаем в ошибку с инкрементом блокировки
			default     => throw new cs_JoinLinkNotFound("link not found"),
		};

		// форматируем ответ
		return [
			"action"      => $action,
			"action_data" => $action_data,
		];
	}

	/**
	 * Метод для запроса инвайта по ссылке
	 *
	 * @param int    $user_id
	 * @param string $link
	 * @param string $session_uniq
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
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 *
	 * @long большой ответ
	 */
	public static function validateJoinLink(int $user_id, string $link, string $session_uniq):array {

		// валидируем ссылку-приглашение
		try {

			// в зависимости от версии запрошенного метода – вызываем нужный обработчик
			/** @var Struct_Db_PivotData_CompanyJoinLinkRel $invite_link_rel */
			/** @var Struct_Db_PivotUser_User $user_info */
			/** @var Struct_Db_PivotCompany_Company $company */
			/** @var bool $is_postmoderation */
			[
				$invite_link_rel, $company, $inviter_user_info, $entry_option, $is_postmoderation, $is_waiting_for_postmoderation, $is_exit_status_in_progress,
			] = self::_validateJoinLinkV4($user_id, $link);

			// добавляем в историю валидацию ссылки
			Domain_Company_Entity_JoinLink_ValidateHistory::add($user_id, $invite_link_rel->join_link_uniq, $session_uniq, $link);
		} catch (\Exception $e) {

			// добавляем в историю ошибку валидации ссылки
			Domain_Company_Entity_JoinLink_ValidateHistory::add($user_id, "", $session_uniq, $link);
			throw $e;
		}

		$inviter_user_info = Struct_User_Info::createStruct($inviter_user_info);

		// формирует ответ
		return [
			"join_link_uniq"                => (string) $invite_link_rel->join_link_uniq,
			"company_id"                    => (int) $company->company_id,
			"company_name"                  => (string) $company->name,
			"company_avatar_color_id"       => (int) $company->avatar_color_id,
			"company_members_count"         => (int) Domain_Company_Entity_Company::getMemberCount($company->extra),
			"inviter_full_name"             => (string) $inviter_user_info->full_name,
			"inviter_avatar_file_key"       => (string) isEmptyString($inviter_user_info->avatar_file_map) ? "" : Type_Pack_File::doEncrypt($inviter_user_info->avatar_file_map),
			"inviter_avatar_color"          => (string) \BaseFrame\Domain\User\Avatar::getColorOutput($inviter_user_info->avatar_color_id),
			"entry_option"                  => (int) $entry_option,
			"is_postmoderation"             => (int) $is_postmoderation,
			"is_waiting_for_postmoderation" => (int) $is_waiting_for_postmoderation,
			"is_exit_status_in_progress"    => (int) $is_exit_status_in_progress,
			"company_avatar_file_key"       => (string) isEmptyString($company->avatar_file_map) ? "" : Type_Pack_File::doEncrypt($company->avatar_file_map),
		];
	}

	/**
	 * Валидирует ссылку.
	 *
	 * @param int    $user_id
	 * @param string $link
	 *
	 * @return array
	 *
	 * @throws Domain_Link_Exception_SupportCompanyTemporarilyUnavailable
	 * @throws Domain_Company_Exception_IsHibernated
	 * @throws Domain_Company_Exception_IsRelocating
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_JoinLinkNotFound
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserNotFound
	 */
	protected static function _validateJoinLinkV4(int $user_id, string $link):array {

		try {

			return Domain_Company_Action_JoinLink_ValidateV4::do($user_id, $link);
		} catch (cs_JoinLinkIsUsed|cs_JoinLinkIsNotActive $e) {

			// выбрасываем другое исключение, если это попытка вступить в компанию поддержки партнерской программы
			self::_throwOnAttemptJoinToSupportCompany($link);

			throw $e;
		}
	}

	/**
	 * Выбрасываем исключение, если пытаются присоединиться в компанию паддержки партнерской программы
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	protected static function _throwOnAttemptJoinToSupportCompany(string $link):void {

		// если партнерская программа отключена, то ничего не делаем
		if (!IS_PARTNER_WEB_ENABLED || ServerProvider::isOnPremise()) {
			return;
		}

		// если это попытка присоединиться в компанию поддержки, то обрабатываем сценарий по-особому
		[$support_company_join_link, $valid_till] = Gateway_Socket_Partner::getSupportCompanyLink();

		if ($support_company_join_link === $link) {
			throw new Domain_Link_Exception_SupportCompanyTemporarilyUnavailable($valid_till);
		}
	}
}
