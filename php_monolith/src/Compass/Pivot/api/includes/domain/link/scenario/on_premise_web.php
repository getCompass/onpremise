<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\CompanyNotServedException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Сценарии для работы со ссылками через веб-сайт on-premise решений
 */
class Domain_Link_Scenario_OnPremiseWeb {

	/**
	 * Выполняет предварительный парсинг ссылки-приглашения
	 * для получения данных о пригласившем пользователе и уникальном идентификаторе.
	 *
	 * @throws Domain_Link_Exception_TemporaryUnavailable
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_JoinLinkNotFound
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserNotFound
	 */
	public static function prepare(int $user_id, string $raw_join_link):Struct_Link_ValidationResult {

		try {

			// пытаемся распарсить текст со ссылкой
			[, $parsed_link] = Domain_Link_Action_Parse::do($raw_join_link);

			if (!is_string($parsed_link) || $parsed_link === "") {
				throw new ParamException("passed incorrect join link");
			}

			// получаем детальную информацию о ссылке
			$join_link_rel_row = Domain_Company_Entity_JoinLink_Main::getByLink($parsed_link);
		} catch (Domain_Link_Exception_LinkNotFound) {
			throw new ParamException("passed incorrect join link");
		}

		return $user_id === 0
			? Domain_Link_Entity_Link::validateBeforeRegistration($join_link_rel_row)
			: Domain_Link_Entity_Link::validateForUser($user_id, $join_link_rel_row);
	}

	/**
	 * Выполняет принятие приглашения в пространство по ссылке-приглашению.
	 *
	 * @throws Domain_Link_Exception_TemporaryUnavailable
	 * @throws CompanyNotServedException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_ExitTaskInProgress
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_JoinLinkNotFound
	 * @throws cs_Text_IsTooLong
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserNotFound
	 */
	public static function accept(int $user_id, string $join_link_uniq, string $pivot_session_uniq):array {

		try {
			$join_link_rel_row = Gateway_Db_PivotData_CompanyJoinLinkRel::get($join_link_uniq);
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("join link not found");
		}

		// проверяем ссылку на возможность принятия пользователем
		$validation_result  = Domain_Link_Entity_Link::validateForUser($user_id, $join_link_rel_row);
		$invite_accept_info = [
			$join_link_rel_row,
			Gateway_Bus_PivotCache::getUserInfo($user_id),
			$validation_result
		];

		/** @var Struct_Dto_Socket_Company_AcceptJoinLinkResponse $accept_result */
		[$user_company, $accept_result] = Domain_Link_Action_Accept::run($user_id, $invite_accept_info, $pivot_session_uniq);
		return [$user_company, $accept_result];
	}
}
