<?php

namespace Compass\Pivot;

/**
 * Класс для взаимодействия со ссылками приглашениям в компанию
 */
class Domain_Company_Entity_JoinLink_Main {

	// дозволенные символы для создания имени ссылки
	public const ALLOWED_ALPHABET_FOR_JOIN_LINK_UNIQ = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

	protected const _PREG_REGEXP = "([a-zA-Z\d]+)";

	// статусы-алиасы ссылки-инвайта (берет начало в модуле php_company)
	protected const _STATUS_ALIAS_ACTIVE = 1; // ссылка активна и доступна для использования
	protected const _STATUS_ALIAS_USED   = 2; // ссылка уже использована

	/**
	 * Достаем приглашение по ссылке
	 *
	 * @throws cs_JoinLinkNotFound|cs_IncorrectJoinLink
	 */
	public static function getByLink(string $link):Struct_Db_PivotData_CompanyJoinLinkRel {

		// пробуем получить join_link_uniq из ссылки
		$matches = [];
		preg_match("/\/join\/" . self::_PREG_REGEXP . "/", $link, $matches);

		// если не нашли строку для join_link_uniq
		if (!isset($matches[1]) || substr_count($link, "http") > 1) {
			throw new cs_IncorrectJoinLink();
		}

		$join_link_uniq = $matches[1];

		// если переданы некорректные символы
		self::assertJoinLinkUniq($join_link_uniq);

		try {
			return Gateway_Db_PivotData_CompanyJoinLinkRel::get($join_link_uniq);
		} catch (\cs_RowIsEmpty) {
			throw new cs_JoinLinkNotFound();
		}
	}

	/**
	 * Проверяем что это join ссылка
	 */
	public static function isJoinLink(string $link):bool {

		// пробуем получить join_link_uniq из ссылки
		$matches = [];
		preg_match("/\/join\/" . self::_PREG_REGEXP . "/", $link, $matches);

		// если не нашли строку для join_link_uniq
		if (!isset($matches[1]) || substr_count($link, "http") > 1) {
			return false;
		}

		$join_link_uniq = $matches[1];

		// если переданы некорректные символы
		if (!preg_match("/" . self::_PREG_REGEXP . "/", $join_link_uniq)) {
			return false;
		}

		return true;
	}

	/**
	 * Выбрасываем исключение если передан некорректный join_link_uniq
	 *
	 * @throws cs_IncorrectJoinLink
	 */
	public static function assertJoinLinkUniq(string $join_link_uniq):void {

		if (!preg_match("/" . self::_PREG_REGEXP . "/", $join_link_uniq)) {
			throw new cs_IncorrectJoinLink();
		}
	}

	/**
	 * Проверяем, что ссылка-приглашение активна
	 *
	 * @return bool
	 */
	public static function isJoinLinkStatusActive(Struct_Db_PivotData_CompanyJoinLinkRel $invite_link_rel):bool {

		return $invite_link_rel->status_alias === self::_STATUS_ALIAS_ACTIVE;
	}

	/**
	 * Выбрасываем исключение если ссылка-приглашение неактивна
	 *
	 * @throws cs_JoinLinkIsNotActive
	 */
	public static function assertJoinLinkStatusActive(Struct_Db_PivotData_CompanyJoinLinkRel $invite_link_rel):void {

		if (!self::isJoinLinkStatusActive($invite_link_rel)) {
			throw new cs_JoinLinkIsNotActive();
		}
	}

	/**
	 * Выбрасываем исключение если ссылка-приглашение неактивна
	 *
	 * @throws cs_JoinLinkIsUsed
	 */
	public static function assertJoinLinkStatusNotUsed(Struct_Db_PivotData_CompanyJoinLinkRel $invite_link_rel):void {

		if ($invite_link_rel->status_alias == self::_STATUS_ALIAS_USED) {
			throw new cs_JoinLinkIsUsed();
		}
	}

	/**
	 * Получаем данные ссылки-приглашения
	 *
	 * @throws \busException
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public static function getJoinLinkInfo(Struct_Db_PivotCompany_Company $company, int $user_id, string $join_link_uniq):array {

		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		[$entry_option, $is_postmoderation, $inviter_user_id, $is_exit_status_in_progress, $was_member, $role] = Gateway_Socket_Company::getInviteLinkInfo(
			$user_id, $join_link_uniq, $company->company_id, $company->domino_id, $private_key
		);

		// для тестов только для тестовых серверов бэкенда
		if (isBackendTest() && Type_System_Testing::isForceExitTaskNotExist()) {
			$is_exit_status_in_progress = 0;
		}

		$user_info = Gateway_Bus_PivotCache::getUserInfo($inviter_user_id);

		return [$user_info, $entry_option, $is_postmoderation, $is_exit_status_in_progress, $was_member, $role];
	}

	/**
	 * Получаем данные ссылки-приглашения для участника
	 *
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_UserNotFound
	 */
	public static function getJoinLinkInfoForMember(Struct_Db_PivotCompany_Company $company, int $user_id, string $join_link_uniq):array {

		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		[$entry_option, $is_postmoderation, $inviter_user_id, $is_exit_status_in_progress, $was_member, $role] = Gateway_Socket_Company::getJoinLinkInfoForMember(
			$user_id, $join_link_uniq, $company->company_id, $company->domino_id, $private_key
		);

		$user_info = Gateway_Bus_PivotCache::getUserInfo($inviter_user_id);

		return [$user_info, $entry_option, $is_postmoderation, $is_exit_status_in_progress, $was_member, $role];
	}

	/**
	 * Получаем ID пользователя создателя ссылки приглашения
	 *
	 * @return number
	 */
	public static function getJoinLinkCreatorUserId(Struct_Db_PivotCompany_Company $company, string $join_link_uniq):int {

		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		return Gateway_Socket_Company::getInviteLinkCreatorUserId($join_link_uniq, $company->company_id, $company->domino_id, $private_key);
	}
}