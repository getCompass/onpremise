<?php

namespace Compass\Company;

use BaseFrame\Server\ServerProvider;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Action для отправки данных аналитики по пространству
 */
class Domain_Company_Action_SendSpaceAnalytics {

	/**
	 * Выполняем
	 *
	 * @long
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \queryException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function do(int $action, int $space_status = Type_Space_Analytics::ANALYTICS_ACTIVE_SPACE_STATUS):void {

		if ((isTestServer() && !isBackendTest() && !isLocalServer()) || ServerProvider::isOnPremise()) {
			return;
		}

		// достаём значения
		$config_list      = Domain_Company_Entity_Config::getList([Domain_Company_Entity_Config::COMPANY_CREATED_AT]);
		$space_created_at = $config_list[Domain_Company_Entity_Config::COMPANY_CREATED_AT]["value"];

		/** @var \CompassApp\Domain\Member\Struct\Main[] $user_list */
		$user_list = Domain_User_Action_Member_GetUserRoleList::do([Member::ROLE_MEMBER, Member::ROLE_ADMINISTRATOR]);

		$member_id_list        = [];
		$administrator_id_list = [];
		foreach ($user_list as $user) {

			if ($user->role == Member::ROLE_MEMBER) {

				$member_id_list[] = $user->user_id;
				continue;
			}

			if ($user->role == Member::ROLE_ADMINISTRATOR) {
				$administrator_id_list[] = $user->user_id;
			}
		}

		// сходим на пивот за статусом тарифа, количеством мест в пространстве и создателем пространства
		[$user_id_creator, $tariff_status, $max_member_count, $user_id_payer_list, $space_deleted_at] = Gateway_Socket_Pivot::getSpaceAnalyticsInfo();

		try {

			$last_wakeup_at          = \CompassApp\Gateway\Db\CompanyData\CompanyDynamic::getValue(ShardingGateway::class, "last_wakeup_at");
			$hibernation_delay_token = Gateway_Db_CompanyData_HibernationDelayTokenList::getLastActivity();

			// получаем последнюю активность компании
			$hibernation_delayed_time = \CompassApp\Company\HibernationHandler::instance()->hibernationDelayedTime();
			if ($last_wakeup_at > time() - DAY14) {
				$last_active_at = $hibernation_delay_token->hibernation_delayed_till - $hibernation_delayed_time;
			} else {
				$last_active_at = $hibernation_delay_token->hibernation_delayed_till - $hibernation_delayed_time * 2;
			}
		} catch (\cs_RowIsEmpty) {
			$last_active_at = 0;
		}

		Type_Space_Analytics::send(
			COMPANY_ID,
			$action,
			$space_status,
			$tariff_status,
			$max_member_count,
			count($user_list),
			$space_created_at,
			$space_deleted_at,
			$last_active_at,
			$member_id_list,
			$user_id_creator,
			$administrator_id_list,
			$user_id_payer_list
		);
	}
}
