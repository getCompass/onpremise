<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс содержит описание действия по автоматическому вступлению пользователя в первую команду
 * on-premise окружения
 *
 * @package Compass\Pivot
 */
class Domain_User_Action_AutoJoin {

	/**
	 * выполняем действие
	 *
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \userAccessException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_ExitTaskInProgress
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_Text_IsTooLong
	 * @throws cs_UserNotFound
	 */
	public static function do(int $user_id, string $pivot_session_uniq):void {

		// получаем тип автоматического вступления в команду
		$auto_join_type = Domain_User_Entity_Auth_Config::getAutoJoinToTeam();

		// если отключено, то ничего не делаем
		if ($auto_join_type === Domain_User_Entity_Auth_Config_AutoJoinEnum::DISABLED) {
			return;
		}

		// получаем user_id root-пользователя
		$root_user_id = Domain_User_Entity_OnpremiseRoot::getUserId();

		// получаем первую активную компанию
		$company = self::_getFirstActiveCompany();

		// делаем запрос на получение информации по ссылке
		$validation_result  = Domain_Link_Entity_Link::getForAutoJoin($company, $user_id, $root_user_id, $auto_join_type);
		$invite_accept_info = [
			$validation_result->invite_link_rel,
			Gateway_Bus_PivotCache::getUserInfo($user_id),
			$validation_result,
		];

		// делаем запрос в компанию на автоматическое вступление пользователя
		Domain_Link_Action_Accept::run($user_id, $invite_accept_info, $pivot_session_uniq);
	}

	/**
	 * получаем первую активную компанию
	 *
	 * @return Struct_Db_PivotCompany_Company
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	protected static function _getFirstActiveCompany():Struct_Db_PivotCompany_Company {

		$company_list = Gateway_Db_PivotCompany_CompanyList::getByStatusList([Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE], 1, 0);
		if (count($company_list) !== 1) {
			throw new ParseFatalException("unexpeceted behaviour");
		}

		return $company_list[0];
	}

}