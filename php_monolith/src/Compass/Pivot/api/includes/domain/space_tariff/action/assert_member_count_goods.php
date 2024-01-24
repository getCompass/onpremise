<?php

namespace Compass\Pivot;

/**
 * Проверяем, что можно активировать продукт
 */
class Domain_SpaceTariff_Action_AssertMemberCountGoods {

	protected const _MAX_DURATION_DELTA = DAY1 * 365 * 3;

	/**
	 * Выполняем
	 *
	 * @param bool $external_activation                  попытка активации goods_id из вне (CRM)
	 * @param bool $activation_additional_days_forbidden запрещена ли активация дополнительных дней (например за активацию goods_id в первые 30 дней команды)
	 *
	 * @throws Domain_Company_Exception_IsDeleted
	 * @throws Domain_SpaceTariff_Exception_IsNotAvailableForSpace
	 * @throws Domain_SpaceTariff_Exception_TimeLimitReached
	 * @throws Domain_User_Exception_IsNotSpaceAdministrator
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 */
	public static function do(Struct_SpaceTariff_ActivationItem $activation_item, bool $need_check_duration, bool $external_activation = false, bool $activation_additional_days_forbidden = false):array {

		$space = Domain_Company_Entity_Company::get($activation_item->space_id);

		[$is_admin, $space_created_at] = Gateway_Socket_Company::getInfoForPurchase($activation_item->customer_user_id, $space);

		// если плательщик не админ и это не активация из вне
		if (!$is_admin && !$external_activation) {
			throw new Domain_User_Exception_IsNotSpaceAdministrator("is not space administrator");
		}

		// если компания удалена - возвращаем ошибку
		if ($space->is_deleted) {
			throw new Domain_Company_Exception_IsDeleted("company is deleted");
		}

		return self::_checkAlterationAvailability($space, $space_created_at, $activation_item, $need_check_duration, $activation_additional_days_forbidden);
	}

	/**
	 * Проверить возможность активировать продукт
	 *
	 * @throws Domain_SpaceTariff_Exception_IsNotAvailableForSpace
	 * @throws Domain_SpaceTariff_Exception_TimeLimitReached
	 * @throws cs_CompanyIncorrectCompanyId
	 *
	 * @long
	 */
	protected static function _checkAlterationAvailability(
		Struct_Db_PivotCompany_Company    $space,
		int                               $space_created_at,
		Struct_SpaceTariff_ActivationItem $activation_item,
		bool                              $need_check_duration,
		bool                              $activation_additional_days_forbidden
	):array {

		$tariff_rows = Gateway_Db_PivotCompany_TariffPlan::getBySpace($activation_item->space_id);
		$tariff      = Domain_SpaceTariff_Tariff::load($tariff_rows);

		$payment_id_list = [];
		if (count($tariff_rows) > 0) {

			$tariff_rows     = array_filter($tariff_rows, fn(array $v) => isset($v["payment_info"]["data"]["payment_id"]));
			$payment_id_list = array_map(fn(array $v) => $v["payment_info"]["data"]["payment_id"], $tariff_rows);
		}

		$circumstance = new \Tariff\Plan\MemberCount\Circumstance(
			Domain_Company_Entity_Company::getMemberCount($space->extra),
			Domain_SpaceTariff_Action_GetPostPaymentPeriod::do()
		);

		// если активация дополнительных дней не запрещена и покупка совершена в первые 30 дней существования компании - добавляем еще 30 дней сверху
		if (!$activation_additional_days_forbidden
			&& ($space_created_at >= time() - DAY1 * 30 && $activation_item->alteration->isActivation() && $tariff->memberCount()->isTrialAvailable(time()))) {

			$activation_item->alteration->setProlongation(
				\Tariff\Plan\BaseAlteration::PROLONGATION_RULE_EXTEND,
				$activation_item->alteration->prolongation_value + DAY1 * 30);
		}

		$alteration = $tariff->memberCount()->arrangeAlteration($activation_item->alteration, $circumstance, time(),
			\Tariff\Plan\BaseAction::METHOD_PAYMENT);

		// если недоступен - возвращаем ошибку
		if (!$alteration->availability->isAvailable()) {
			throw new Domain_SpaceTariff_Exception_IsNotAvailableForSpace($alteration->availability->getMessage());
		}

		if ($need_check_duration && !static::_checkDuration($tariff->memberCount(), $alteration)) {
			throw new Domain_SpaceTariff_Exception_TimeLimitReached("duration limit exceeded");
		}

		return [$alteration, $payment_id_list];
	}

	/**
	 * Проверяем ограничение длительности.
	 */
	protected static function _checkDuration(\Tariff\Plan\MemberCount\MemberCount $plan, \Tariff\Plan\MemberCount\Alteration $alteration):bool {

		$limit = time() + static::_MAX_DURATION_DELTA;

		// если это установка даты
		// то просто не даем ей перевалить за лимит
		if ($alteration->prolongation_value > $limit && $alteration->isProlongationSet()) {
			return false;
		}

		// если это продление от текущей даты
		// то смотрим, чтобы сумма не ушла за лимит
		if ($alteration->isProlongationExtend()) {

			$active_till = max($plan->getActiveTill(), time()) + $alteration->prolongation_value;

			if ($active_till > $limit) {
				return false;
			}
		}

		return true;
	}
}