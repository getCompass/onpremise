<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Действие отправки лога текущего статуса пространства
 */
class Domain_Analytic_Action_SendSpaceStatusLog {

	protected const _COMPANY_LIST_CHUNK = 10;

	/**
	 * выполняем
	 */
	public static function do():void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$offset = 0;
		do {
			$company_list = Gateway_Db_PivotCompany_CompanyList::getActiveList(self::_COMPANY_LIST_CHUNK, $offset);
			$offset       += count($company_list);

			foreach ($company_list as $company) {

				try {

					Domain_User_Scenario_Phphooker::onSendSpaceStatusLog($company->company_id, Type_Space_Analytics::CRON_UPDATE);
				} catch (\Exception $e) {
					Type_System_Admin::log("analytic-company-cron-log", $e);
				}
			}

			usleep(0.2 * 1000 * 1000);
		} while (count($company_list) > 0);
	}
}