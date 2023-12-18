<?php

namespace Compass\Pivot;

/**
 * Сценарии точки входа socket для домена тарифного плана пространства.
 */
class Domain_SpaceTariff_Scenario_Socket {

	/**
	 * Константы для возможных значений payment_method, присылаемые биллингом
	 */
	protected const _BILLING_PAYMENT_METHOD_CRM = "crm";

	/**
	 * Проверяет, можно ли активировать выбранный продукт.
	 *
	 * @throws Domain_Company_Exception_IsDeleted
	 * @throws Domain_SpaceTariff_Exception_IsNotAvailableForSpace
	 * @throws Domain_SpaceTariff_Exception_TimeLimitReached
	 * @throws Domain_User_Exception_IsNotSpaceAdministrator
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 */
	public static function assertGoods(string $goods_id, string $payment_method = ""):void {

		// пытаемся получить подходящий тарифный план для изменения
		$activation_item = Domain_SpaceTariff_Entity_ActivationResolver::resolve($goods_id);

		if ($activation_item === false) {
			throw new \BaseFrame\Exception\Request\ParamException("passed incorrect goods_id");
		}

		// получаем флаги активации продукта в зависимости от параметров активации
		[$external_activation, $activation_additional_days_forbidden] = self::_resolveActivationFlags($payment_method);

		Domain_SpaceTariff_Action_AssertMemberCountGoods::do($activation_item, true, $external_activation, $activation_additional_days_forbidden);
	}

	/**
	 * Является ли администратором пространства
	 *
	 * @param int $user_id
	 * @param int $space_id
	 *
	 * @return bool
	 * @throws Domain_Company_Exception_IsDeleted
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function isSpaceAdmin(int $user_id, int $space_id):bool {

		// получаем компанию
		$space = Domain_Company_Entity_Company::get($space_id);

		// если компания удалена - возвращаем ошибку
		if ($space->is_deleted) {
			throw new Domain_Company_Exception_IsDeleted("company is deleted");
		}

		[$is_admin,] = Gateway_Socket_Company::getInfoForPurchase($user_id, $space);

		return $is_admin;
	}

	/**
	 * Активировать продукт
	 *
	 * @throws Domain_Company_Exception_IsDeleted
	 * @throws Domain_SpaceTariff_Exception_AlterationUnsuccessful
	 * @throws Domain_SpaceTariff_Exception_DuplicatePayment
	 * @throws Domain_SpaceTariff_Exception_IsNotAvailableForSpace
	 * @throws Domain_SpaceTariff_Exception_TimeLimitReached
	 * @throws Domain_User_Exception_IsNotSpaceAdministrator
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 */
	public static function activateSpacePurchase(string $goods_id, string $payment_id, int $payed_amount = 0, string $payed_currency = "", int $net_amount_rub = 0, string $payment_method = "", string $payment_user_agent = "", int $payment_price_type = 0):void {

		// пытаемся получить подходящий тарифный план для изменения
		$activation_item = Domain_SpaceTariff_Entity_ActivationResolver::resolve($goods_id);

		if ($activation_item === false) {
			throw new \BaseFrame\Exception\Request\ParamException("passed incorrect goods_id");
		}

		// получаем флаги активации продукта в зависимости от параметров активации
		[$external_activation, $activation_additional_days_forbidden] = self::_resolveActivationFlags($payment_method);

		[$alteration, $payment_id_list] = Domain_SpaceTariff_Action_AssertMemberCountGoods
			::do($activation_item, false, $external_activation, $activation_additional_days_forbidden);

		// если предыдущий платеж имеет такой же id - отклоняем
		if (in_array($payment_id, $payment_id_list)) {
			throw new Domain_SpaceTariff_Exception_DuplicatePayment("duplicate payment");
		}

		$tariff = Domain_SpaceTariff_Action_AlterMemberCount::run(
			$activation_item->customer_user_id,
			$activation_item->space_id,
			\Tariff\Plan\BaseAction::METHOD_PAYMENT,
			$alteration,
			$payment_id,
			Domain_SpaceTariff_Action_AlterMemberCount::REASON_SELF_DEFAULT,
			$payed_amount,
			$payed_currency,
			$net_amount_rub,
			$payment_method,
			$payment_user_agent,
		);

		// оповещаем CRM о новой оплате
		self::_notifyCrmOnPayment($activation_item, $payment_id, $payed_currency, $payed_amount, $net_amount_rub, $payment_method, $payment_price_type, $tariff);
	}

	/**
	 * Получаем флаги активации продукта в зависимости от параметров активации
	 *
	 * @return bool[]
	 */
	protected static function _resolveActivationFlags(string $payment_method):array {

		// значения по умолчанию
		$external_activation                  = false;
		$activation_additional_days_forbidden = false;

		// если активация продукта происходит из CRM
		if ($payment_method === self::_BILLING_PAYMENT_METHOD_CRM) {

			$external_activation                  = true;
			$activation_additional_days_forbidden = true;
		}

		return [$external_activation, $activation_additional_days_forbidden];
	}

	/**
	 * Оповещаем CRM о новой оплате
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	protected static function _notifyCrmOnPayment(Struct_SpaceTariff_ActivationItem $activation_item,
								    string                            $payment_id,
								    string                            $payed_currency,
								    int                               $payed_amount,
								    int                               $net_amount_rub,
								    string                            $payment_method,
								    int                               $payment_price_type,
								    Domain_SpaceTariff_Tariff         $tariff):void {

		// если активировали тариф из CRM, то оповещать не стоит
		if ($payment_method === self::_BILLING_PAYMENT_METHOD_CRM) {
			return;
		}

		Gateway_Socket_Crm::onNewPayment(
			$activation_item->space_id,
			$payment_id,
			$payed_currency,
			$payed_amount,
			$net_amount_rub,
			$payment_method,
			$payment_price_type,
			$tariff->memberCount()->getLimit(),
			$activation_item->alteration->prolongation_value,
			$tariff->memberCount()->getActiveTill(),
			$tariff->memberCount()->isTrial(time()),
			$tariff->memberCount()->isActive(time()) && !$tariff->memberCount()->isFree(time()),
		);
	}
}