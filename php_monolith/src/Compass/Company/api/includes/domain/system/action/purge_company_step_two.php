<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Class Domain_System_Action_Event_PurgeCompany
 */
class Domain_System_Action_PurgeCompanyStepTwo {

	/**
	 * Выполняет очистку данных компании.
	 */
	public static function run():void {

		Type_System_Admin::log("purge", "чищу базу");
		Domain_System_Entity_System::purgeDatabase();

		Type_System_Admin::log("purge", "чищу мемкэш");
		\Compass\Company\ShardingGateway::cache()->flush();

		Type_System_Admin::log("purge", "чищу рейтинг, второй раз");
		Gateway_Bus_Company_Rating::doClearCache();

		Type_System_Admin::log("purge", "чищу кэш сессий");
		Gateway_Bus_CompanyCache::clearSessionCache();

		Type_System_Admin::log("purge", "чищу кэш пользователей");
		Gateway_Bus_CompanyCache::clearMemberCache();

		Type_System_Admin::log("purge", "чищу кэш конфига");
		Gateway_Bus_CompanyCache::clearConfigCache();

		Type_System_Admin::log("purge", "почищено " . COMPANY_ID);
	}
}
