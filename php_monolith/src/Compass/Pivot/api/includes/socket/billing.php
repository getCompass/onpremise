<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * Контроллер для работы с биллингом
 */
class Socket_Billing extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"assertGoods",
		"isSpaceAdmin",
		"activateSpacePurchase",
	];

	/**
	 * Проверяем, что товар можно активировать
	 */
	public function assertGoods():array {

		$goods_id       = $this->post(\Formatter::TYPE_STRING, "goods_id");
		$payment_method = $this->post(\Formatter::TYPE_STRING, "payment_method", "");

		try {
			Domain_SpaceTariff_Scenario_Socket::assertGoods($goods_id, $payment_method);
		} catch (cs_CompanyNotExist|Domain_Company_Exception_IsDeleted|cs_CompanyIncorrectCompanyId|Gateway_Socket_Exception_CompanyIsNotServed) {
			return $this->error(1412003, "cant activate goods for space");
		} catch (Domain_SpaceTariff_Exception_IsNotAvailableForSpace) {
			return $this->error(1412004, "goods are not available for space");
		} catch (Domain_User_Exception_IsNotSpaceAdministrator) {
			return $this->error(1412005, "user is not administrator");
		} catch (Domain_SpaceTariff_Exception_TimeLimitReached) {
			return $this->error(1412002, "duration limit exceeded");
		}

		return $this->ok();
	}

	/**
	 * Проверяем, что пользователь администратор пространства.
	 */
	public function isSpaceAdmin():array {

		$user_id  = $this->post(\Formatter::TYPE_INT, "user_id");
		$space_id = $this->post(\Formatter::TYPE_INT, "space_id");

		if ($space_id < 1 || $user_id < 1) {
			throw new ParamException("incorrect parameters");
		}

		try {
			$is_admin = Domain_SpaceTariff_Scenario_Socket::isSpaceAdmin($user_id, $space_id);
		} catch (cs_CompanyNotExist|Domain_Company_Exception_IsDeleted|cs_CompanyIncorrectCompanyId|Gateway_Socket_Exception_CompanyIsNotServed) {
			return $this->error(1412003, "cant activate goods for space");
		}

		return $this->ok([
			"is_allow" => (bool) $is_admin,
		]);
	}

	/**
	 * Активируем покупку.
	 */
	public function activateSpacePurchase():array {

		$goods_id           = $this->post(\Formatter::TYPE_STRING, "goods_id");
		$payment_id         = $this->post(\Formatter::TYPE_STRING, "payment_id");
		$payed_amount       = $this->post(\Formatter::TYPE_INT, "payed_amount", 0);
		$payed_currency     = $this->post(\Formatter::TYPE_STRING, "payed_currency", "");
		$net_amount_rub     = $this->post(\Formatter::TYPE_INT, "net_amount_rub", 0);
		$payment_method     = $this->post(\Formatter::TYPE_STRING, "payment_method", "");
		$payment_user_agent = $this->post(\Formatter::TYPE_STRING, "payment_user_agent", "");
		$payment_price_type = $this->post(\Formatter::TYPE_INT, "payment_price_type", 0);

		if (!checkUuid($payment_id)) {
			throw new ParamException("invalid payment_id");
		}

		try {

			Domain_SpaceTariff_Scenario_Socket::activateSpacePurchase(
				$goods_id, $payment_id, $payed_amount, $payed_currency, $net_amount_rub, $payment_method, $payment_user_agent, $payment_price_type
			);
		} catch (cs_CompanyNotExist|Domain_Company_Exception_IsDeleted|cs_CompanyIncorrectCompanyId|Gateway_Socket_Exception_CompanyIsNotServed) {
			return $this->error(1412003, "cant activate goods for space");
		} catch (Domain_SpaceTariff_Exception_IsNotAvailableForSpace) {
			return $this->error(1412004, "goods are not available for space");
		} catch (Domain_SpaceTariff_Exception_DuplicatePayment) {
			return $this->error(1412001, "duplicated payment");
		} catch (Domain_User_Exception_IsNotSpaceAdministrator) {
			return $this->error(1412005, "user is not administrator");
		} catch (Domain_SpaceTariff_Exception_TimeLimitReached) {
			return $this->error(1412002, "duration limit exceeded");
		} catch (Domain_SpaceTariff_Exception_AlterationUnsuccessful $e) {

			// если ошибка известна как ошибка-апи
			// то возвращаем для клиентов кастомный код
			if ($e->getKnowSocketError() !== 0) {
				return $this->error($e->getKnowSocketError(), $e->getMessage());
			}

			// если ошибка неизвестная, то бросаем неопределенное исключение
			throw new ParamException($e->getMessage());
		}

		return $this->ok([]);
	}
}