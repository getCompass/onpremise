<?php

namespace Compass\Pivot;

/**
 * Вспомогательный класс для работы с
 * альтерациями тарифных планов пространств.
 */
class Domain_SpaceTariff_Action_AlterMemberCount {

	public const REASON_SELF_DEFAULT   = "default";
	public const REASON_SELF_USER_LEFT = "user_left";
	public const REASON_SELF_USER_JOIN = "user_join";

	/**
	 * Применяет альтерацию к тарифному плану количества участников.
	 *
	 * @throws Domain_SpaceTariff_Exception_TimeLimitReached
	 * @throws Domain_SpaceTariff_Exception_AlterationUnsuccessful
	 * @throws cs_CompanyIncorrectCompanyId
	 *
	 * @long временно
	 */
	public static function run(
		int                                 $user_id,
		int                                 $space_id,
		int                                 $method,
		\Tariff\Plan\MemberCount\Alteration $alteration,
		string                              $payment_id = "",
		string                              $reason = self::REASON_SELF_DEFAULT,
		int                                 $payed_amount = 0,
		string                              $payed_currency = "",
		int                                 $net_amount_rub = 0,
		string                              $payment_method = "",
		string                              $payment_user_agent = "",
	):Domain_SpaceTariff_Tariff {

		/** начинаем транзакцию */
		Gateway_Db_PivotCompany_CompanyList::beginTransaction($space_id);

		try {

			// блокируем компанию на обновление
			$space  = Gateway_Db_PivotCompany_CompanyList::getForUpdate($space_id);
			$tariff = Domain_SpaceTariff_Repository_Tariff::get($space->company_id);
		} catch (\cs_RowIsEmpty $e) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException($e->getMessage());
		}

		$was_restricted = $tariff->memberCount()->isRestricted(time());
		$circumstance   = self::_resolveCircumstance($space, $reason);

		// получаем тип оплаты для аналитики
		$payment_type = self::getPaymentTypeForAnalytics($method, $tariff, $alteration);

		// является тариф бесплатным
		$is_tariff_free = $tariff->memberCount()->isFree(time());

		// выполняем применение изменений под капотом
		$alteration_result = $tariff->memberCount()->applyAlteration($method, $alteration, $circumstance, time());

		// если тариф пространства был не бесплатным и стал бесплатным
		$is_switch_to_free_tariff = !$is_tariff_free && $tariff->memberCount()->isFree(time());

		// если измененный тарифный план вылез за 3 года - возвращаем ошибку
		// во всех случаях, кроме оплаты, оплату всегда разрешаем
		if ($method !== \Tariff\Plan\BaseAction::METHOD_PAYMENT && $alteration->isProlongation() && $tariff->memberCount()->getActiveTill() > time() + DAY1 * 365 * 3) {

			Gateway_Db_PivotCompany_CompanyList::rollback($space->company_id);
			throw new Domain_SpaceTariff_Exception_TimeLimitReached("time limit reached");
		}

		// если вдруг что-то пошло не по плану
		// то считаем, что извне пришли некорректные данные
		if (!$alteration_result->isSuccess()) {

			Gateway_Db_PivotCompany_CompanyList::rollback($space->company_id);
			throw new Domain_SpaceTariff_Exception_AlterationUnsuccessful($alteration_result->getMessage(), $alteration_result->getCode());
		}

		// если были изменения, то применяем их
		if ($tariff->memberCount()->hasChanges()) {

			$payment_info = Domain_SpaceTariff_Entity_PaymentInfo::make(
				$user_id, $payment_id, $method, $alteration->isProlongationExtend() ? $alteration->prolongation_value : 0
			);

			static::_saveChanges($space, $tariff, $payment_info);
		}

		Gateway_Db_PivotCompany_CompanyList::commitTransaction($space->company_id);
		/** завершаем транзакцию */

		// отправляем ивент, чтобы разблокировать пространство
		if ($was_restricted && !$tariff->memberCount()->isRestricted(time())) {
			Domain_Pivot_Entity_Event_SpaceUnblock::create($space->company_id, time() + 5);
		}

		try {

			// отключаем все анонсы, которые висели до этого
			Gateway_Socket_Space_Tariff::disableAnnouncements($space);
		} catch (Gateway_Socket_Exception_CompanyIsNotServed|cs_CompanyIsHibernate) {
			// ничего не делаем
		}

		// отправляем данные для аналитики
		self::_sendAnalytics(
			$user_id, $space->company_id, $payment_type, $tariff, $method, $is_switch_to_free_tariff,
			$payed_amount, $payed_currency, $net_amount_rub, $payment_method, $payment_user_agent
		);

		// оповещаем об изменении тарифного плана в пространстве
		Domain_Crm_Entity_Event_SpaceTariffAltered::create($space_id);
		Domain_Partner_Entity_Event_SpaceTariffAltered::create($space_id);

		return $tariff;
	}

	/**
	 * @param Struct_Db_PivotCompany_Company $space
	 * @param string                         $reason
	 *
	 * @return \Tariff\Plan\MemberCount\Circumstance
	 */
	protected static function _resolveCircumstance(Struct_Db_PivotCompany_Company $space, string $reason):\Tariff\Plan\MemberCount\Circumstance {

		$member_count        = Domain_Company_Entity_Company::getMemberCount($space->extra);
		$post_payment_period = Domain_SpaceTariff_Action_GetPostPaymentPeriod::do();

		return match ($reason) {
			static::REASON_SELF_DEFAULT   => new \Tariff\Plan\MemberCount\Circumstance($member_count, $post_payment_period),
			static::REASON_SELF_USER_LEFT => new \Tariff\Plan\MemberCount\Circumstance($member_count - 1, $post_payment_period),
			static::REASON_SELF_USER_JOIN => new \Tariff\Plan\MemberCount\Circumstance($member_count + 1, $post_payment_period),
			default                       => throw new \BaseFrame\Exception\Domain\ParseFatalException("passed unknown reason")
		};
	}

	/**
	 * Выполняет сохранение тарифного плана.
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	protected static function _saveChanges(Struct_Db_PivotCompany_Company $space, Domain_SpaceTariff_Tariff $tariff, array $payment_info):void {

		// сохраняем изменения
		/** @noinspection PhpUnusedLocalVariableInspection */
		[$plan, $tariff_plan_id, $tariff_plan_history_id] = Domain_SpaceTariff_Repository_Tariff::updateMemberCount($space->company_id, $tariff->memberCount(), $payment_info);

		try {
			$space_config = Domain_Domino_Entity_Config::get($space);
		} catch (Domain_Company_Exception_ConfigNotExist) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("space config not found");
		}

		$save_data     = $tariff->memberCount()->getData();
		$tariff_config = Domain_Domino_Entity_Config::makeTariff($save_data);

		$space_config->setTariff($tariff_config);

		Domain_Domino_Entity_Config::update($space->company_id, $space_config);
		Domain_Space_Entity_Tariff_PlanObserver::add($space->company_id);

		// логируем оплату в истории
		Domain_SpaceTariff_Entity_PaymentHistory::log($space, $payment_info, $tariff_plan_history_id);
	}

	/**
	 * получаем тип оплаты для аналитики
	 */
	public static function getPaymentTypeForAnalytics(int $method, Domain_SpaceTariff_Tariff $tariff, \Tariff\Plan\MemberCount\Alteration $alteration):string {

		// если это не оплата, то пропускаем
		if ($method !== \Tariff\Plan\BaseAction::METHOD_PAYMENT) {
			return "";
		}

		if ($tariff->memberCount()->getExtendPolicyRule() !== \Tariff\Plan\MemberCount\OptionExtendPolicy::NEVER) {

			// если тариф триала
			if ($tariff->memberCount()->isTrial(time()) || $tariff->memberCount()->isTrialAvailable(time())) {
				return Domain_SpaceTariff_Action_SendAnalytic::PAYMENT_TYPE_FROM_TRIAL;
			}

			// если бесплатный
			if ($tariff->memberCount()->isFree(time())) {
				return Domain_SpaceTariff_Action_SendAnalytic::PAYMENT_TYPE_FROM_FREE;
			}

			// первая оплата
			return Domain_SpaceTariff_Action_SendAnalytic::PAYMENT_TYPE_FIRST_PAYMENT;
		} elseif ($alteration->isChange() && !$alteration->isActivation()) {

			// произошло изменение тарифа
			return Domain_SpaceTariff_Action_SendAnalytic::PAYMENT_TYPE_TARIFF_CHANGE;
		} elseif ($alteration->isProlongation()) {

			// продление срока
			return Domain_SpaceTariff_Action_SendAnalytic::PAYMENT_TYPE_PROLONGATION;
		}

		return "";
	}

	/**
	 * отправляем аналитику для тарифа
	 * @long
	 */
	protected static function _sendAnalytics(int $user_id, int $space_id, string $payment_type, Domain_SpaceTariff_Tariff $tariff, int $method, bool $is_switch_to_free_tariff, int $payed_amount, string $payed_currency, int $net_amount_rub, string $payment_method, string $payment_user_agent):void {

		// если выполнен переход на триал, отправляем в аналитику
		if ($is_switch_to_free_tariff) {
			Type_Space_Analytics::send(Type_Space_Analytics::SWITCH_TO_FREE_TARIFF, $space_id);
		} else {
			Type_Space_Analytics::send(Type_Space_Analytics::PAYED_FOR_SPACE, $space_id);
		}

		if ($method !== \Tariff\Plan\BaseAction::METHOD_PAYMENT) {
			return;
		}

		try {
			$payment_device = Type_Api_Platform::getElectronPlatformOS($payment_user_agent);
		} catch (cs_PlatformNotFound) {
			$payment_device = "";
		}

		$duration          = ceil(($tariff->memberCount()->getActiveTill() - time()) / DAY1);
		$payed_month_count = ceil($duration / 30);
		$payed_tariff      = $tariff->memberCount()->getLimit();

		// отправляем аналитику по оплате
		Domain_SpaceTariff_Action_SendAnalytic::do(
			$user_id,
			$space_id,
			$tariff->memberCount()->getActiveTill(),
			$payed_amount,
			$net_amount_rub,
			$tariff->memberCount()->isTrial(time()),
			0,
			$payed_month_count,
			$payed_tariff,
			$payed_currency,
			"",
			$payment_type,
			$payment_device,
			$payment_method,
		);
	}
}