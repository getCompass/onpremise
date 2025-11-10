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

		$join_link_uniq = static::_parseUniqFromRawLink($link);

		if ($join_link_uniq === false) {
			throw new cs_IncorrectJoinLink("passed incorrect link");
		}

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

		return is_string(static::_parseUniqFromRawLink($link));
	}

	/**
	 * Пытается вытащить из ссылки значение join link uniq.
	 * Если join link uniq в ссылке не найден, вернет false.
	 */
	public static function _parseUniqFromRawLink(string $link):string|false {

		// пробуем получить join_link_uniq из ссылки
		$matches = [];

		// сначала сверяем на соответствие любому из join адресов
		foreach (PUBLIC_ENTRYPOINT_JOIN_VARIETY as $variety) {

			if ($variety === "") {
				continue;
			}

			$variety = str_starts_with($variety, WEB_PROTOCOL_PUBLIC)
				? mb_substr($variety, mb_strlen(WEB_PROTOCOL_PUBLIC) + 3) // +3 для ://
				: $variety;

			preg_match("#" . $variety . "/" . self::_PREG_REGEXP . "#", $link, $matches);

			if (isset($matches[1])) {
				break;
			}
		}

		// если не нашли по точкам входа, используем старую логику с /join
		if (!isset($matches[1])) {

			// еcли не нашли, то пробуем по старому форматированию определить ссылку
			preg_match("#/join/" . self::_PREG_REGEXP . "#", $link, $matches);

			if (!isset($matches[1]) || substr_count($link, "http") > 1) {
				return false;
			}
		}

		if (substr_count($link, "http") > 1) {
			return false;
		}

		if (!static::checkJoinLinkUniq($matches[1])) {
			return false;
		}

		return $matches[1];
	}

	/**
	 * Проверяет, является ли переданное значение корректным join_link_uniq.
	 */
	public static function checkJoinLinkUniq(string $join_link_uniq):bool {

		return preg_match("/" . self::_PREG_REGEXP . "/", $join_link_uniq);
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