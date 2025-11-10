<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\CompanyNotServedException;
use BaseFrame\Exception\Request\ParamException;

/**
 * сценарии smart app для API
 */
class Domain_SmartApp_Scenario_Api {

	/**
	 * Получаем каталог приложений
	 *
	 * @param int $user_id
	 * @param int $company_id
	 * @param int $method_version
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws CompanyNotServedException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_CompanyIsNotActive
	 * @throws cs_CompanyNotExist
	 * @throws cs_UserNotFound
	 * @throws cs_UserNotInCompany
	 */
	public static function getSuggestionList(int $user_id, int $company_id, int $method_version):array {

		if ($user_id < 1 || $company_id < 1) {
			throw new ParamException("incorrect params");
		}

		// проверяем что пользователь существует
		Gateway_Bus_PivotCache::getUserInfo($user_id);

		try {
			$company = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_CompanyNotExist();
		}

		if ($company->status !== Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {
			throw new cs_CompanyIsNotActive("space not active");
		}

		// проверяем состоит ли в пространстве
		try {
			Gateway_Db_PivotUser_CompanyList::getOne($user_id, $company_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_UserNotInCompany();
		}

		return match ($method_version) {

			2 => self::_getSuggestionListV2($user_id, $company),
			default => self::_getSuggestionListV1($user_id, $company),
		};
	}

	/**
	 * Форматируем ответ 1 версии
	 *
	 * @param int                            $user_id
	 * @param Struct_Db_PivotCompany_Company $company
	 *
	 * @return object[]
	 * @throws CompanyNotServedException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_CompanyIsHibernate
	 */
	protected static function _getSuggestionListV1(int $user_id, Struct_Db_PivotCompany_Company $company):array {

		$smart_app_suggested_list   = Domain_SmartApp_Entity_SuggestedCatalog::getSuggestedCatalog();
		$category_localization_list = Domain_SmartApp_Entity_SuggestedCatalog::getCategoryLocalization();

		$private_key            = Domain_Company_Entity_Company::getPrivateKey($company->extra);
		$created_smart_app_list = Gateway_Socket_Company::getCreatedSmartAppListByUser($user_id, $company->company_id, $company->domino_id, $private_key);

		return [
			"smart_app_suggested_list"   => (object) Domain_SmartApp_Action_FormatSuggestionListV1::do($smart_app_suggested_list, $created_smart_app_list),
			"category_localization_list" => (object) $category_localization_list,
		];
	}

	/**
	 * Форматируем ответ 2 версии
	 *
	 * @param int                            $user_id
	 * @param Struct_Db_PivotCompany_Company $company
	 *
	 * @return object[]
	 * @throws CompanyNotServedException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_CompanyIsHibernate
	 */
	protected static function _getSuggestionListV2(int $user_id, Struct_Db_PivotCompany_Company $company):array {

		$smart_app_suggested_list   = Domain_SmartApp_Entity_SuggestedCatalog::getSuggestedCatalog();
		$category_localization_list = Domain_SmartApp_Entity_SuggestedCatalog::getCategoryLocalization();

		$private_key            = Domain_Company_Entity_Company::getPrivateKey($company->extra);
		$created_smart_app_list = Gateway_Socket_Company::getCreatedSmartAppListByUser($user_id, $company->company_id, $company->domino_id, $private_key);

		[$smart_app_list, $category_list] = Domain_SmartApp_Action_FormatSuggestionListV2::do(
			$smart_app_suggested_list, $category_localization_list, $created_smart_app_list
		);

		return [
			"smart_app_list" => (array) $smart_app_list,
			"category_list"  => (object) $category_list,
		];
	}
}
