<?php

namespace Compass\Pivot;

/**
 * Действие, исключающее пользователя из компании.
 * Второй этап в блокировке пользователя.
 */
class Domain_User_Action_KickUserFromSystem {

	/**
	 * Второй этап блокирования пользователя.
	 * Исключаем его из компаний.
	 *
	 */
	public static function run(int $user_id):void {

		// исключаем из анонсов
		Gateway_Announcement_Main::invalidateUser($user_id);

		// получаем все активные компании для пользователя
		$user_company_list = Gateway_Db_PivotUser_CompanyList::getCompanyList($user_id);

		foreach ($user_company_list as $user_company) {

			$company     = Domain_Company_Entity_Company::get($user_company->company_id);
			$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

			try {
				[$description, $badge, $user_role] = Gateway_Socket_Company::getUserInfo($user_id, $company->company_id, $company->domino_id, $private_key);
			} catch (cs_UserNotFound) {
				continue;
			}

			// для каждой компании ставим задачу на исключение
			Type_System_Admin::log("user-kicker", "ставлю задачу на исключение пользователя {$user_id} из компании {$user_company->company_id}");
			Type_Phphooker_Main::onKickUserFromCompanyRequested($user_id, $user_role, $user_company->company_id);
		}
	}
}
