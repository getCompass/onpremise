<?php

namespace Compass\Pivot;

/**
 * Класс для работы с аналитикой статуса пространства
 */
class Type_Space_Analytics {

	protected const _EVENT_KEY = "space";

	public const CREATED               = 1; // создано новое пространство
	public const PAYED_FOR_SPACE       = 2; // произведена оплата за пространство
	public const END_SPACE_TARIFF      = 3; // у пространства закончилась подписка
	public const MEMBER_COUNT_CHANGED  = 4; // изменилось число участников пространства
	public const DELETED               = 5; // пространство удалено
	public const NEW_ADMINISTRATOR     = 6; // назначен новый администратор пространства
	public const DISMISS_ADMINISTRATOR = 7; // администратор разжалован до пользователя
	public const CRON_UPDATE           = 8; // обновляем по крону
	public const SWITCH_TO_FREE_TARIFF = 9; // переключение на бесплатный тарифф

	public const ANALYTICS_ACTIVE_SPACE_STATUS  = 1;
	public const ANALYTICS_DELETED_SPACE_STATUS = 0;

	public const ANALYTICS_FREE_SPACE_TARIFF_STATUS      = 1; // если бесплатный, но не триал
	public const ANALYTICS_TRIAL_SPACE_TARIFF_STATUS     = 2; // если использует триал
	public const ANALYTICS_PAYED_SPACE_TARIFF_STATUS     = 3; // уже оплачено и активно
	public const ANALYTICS_NOT_PAYED_SPACE_TARIFF_STATUS = 4; // закончился период (не оплачено)

	/**
	 * Пишем аналитику по действиям пространства
	 * @long
	 */
	public static function send(int $action, int $company_id):void {

		$company = Domain_Company_Entity_Company::get($company_id);

		$is_active = self::ANALYTICS_ACTIVE_SPACE_STATUS;
		if ($action == self::DELETED) {
			$is_active = self::ANALYTICS_DELETED_SPACE_STATUS;
		}

		// делаем сокет запрос в компанию для отправки лога
		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		$member_count       = 0;
		$last_active_at     = 0;
		$user_id_members    = [];
		$user_id_admin_list = [];
		try {
			[$member_count, $last_active_at, $user_id_members, $user_id_admin_list] = Gateway_Socket_Company::getCompanyAnalytic(
				$company->company_id, $company->domino_id, $private_key);
		} catch (Gateway_Socket_Exception_CompanyIsNotServed|cs_CompanyIsHibernate) {
			// не делаем ничего
		}

		// получаем статус тарифа
		$tariff_rows   = Gateway_Db_PivotCompany_TariffPlan::getBySpace($company_id);
		$tariff        = Domain_SpaceTariff_Tariff::load($tariff_rows);
		$tariff_status = self::getAnalyticTariffStatus($tariff->memberCount());

		// получаем лимит для пользователей в пространстве
		$max_member_count = $tariff->memberCount()->getLimit();

		Gateway_Bus_CollectorAgent::init()->log(self::_EVENT_KEY, [
			"space_id"           => $company->company_id,
			"action"             => $action,
			"tariff_status"      => $tariff_status,
			"space_status"       => $is_active,
			"max_member_count"   => $max_member_count,
			"member_count"       => $member_count,
			"user_id_creator"    => $company->created_by_user_id,
			"space_created_at"   => $company->created_at,
			"space_deleted_at"   => $company->deleted_at,
			"created_at"         => time(),
			"last_active_at"     => $last_active_at,
			"user_id_members"    => $user_id_members,
			"user_id_admin_list" => $user_id_admin_list,
			"user_id_payer_list" => [],
		]);
	}

	/**
	 * получаем статус тарифа для аналитики пространства
	 */
	public static function getAnalyticTariffStatus(\Tariff\Plan\MemberCount\MemberCount $member_count_plan):int {

		if ($member_count_plan->isActive(time())) {

			if ($member_count_plan->isFree(time())) {
				return self::ANALYTICS_FREE_SPACE_TARIFF_STATUS;
			}

			if ($member_count_plan->isTrial(time())) {
				return self::ANALYTICS_TRIAL_SPACE_TARIFF_STATUS;
			}

			return self::ANALYTICS_PAYED_SPACE_TARIFF_STATUS;
		} else {
			return self::ANALYTICS_NOT_PAYED_SPACE_TARIFF_STATUS;
		}
	}
}