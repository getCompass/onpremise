<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Class Domain_System_Action_Event_PurgeCompany
 */
class Domain_System_Action_PurgeCompanyStepOne {

	/**
	 * Выполняет очистку данных компании.
	 */
	public static function run():void {

		Type_System_Admin::log("purge", "начинаю чистку компании " . COMPANY_ID);
		Type_System_Admin::log("purge", "чищу сессии");

		// очищаем session & member кэши
		self::_purgeSessionAndMemberCache();

		Type_System_Admin::log("purge", "чищу мемкэш");
		\Compass\Company\ShardingGateway::cache()->flush();

		Type_System_Admin::log("purge", "чищу рейтинг");
		Gateway_Bus_Company_Rating::doClearCache();
	}

	/**
	 * Очищаем session & member кэши
	 *
	 * @throws \busException
	 */
	protected static function _purgeSessionAndMemberCache():void {

		// разлогиниваем всех и получаем список пользователей для очистки кэша
		$user_id_list = Domain_System_Action_LogoutAll::do();

		foreach ($user_id_list as $user_id) {
			Gateway_Bus_CompanyCache::clearMemberCacheByUserId($user_id);
		}
	}
}
