<?php

namespace Compass\Company;

/**
 * Класс действия при удалении запроса на оплату
 */
class Domain_Premium_Action_PaymentRequest_Delete {

	/**
	 * выполняем
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do(int $user_id):void {

		// пробуем получить запись запроса на оплату
		$payment_request = Domain_Premium_Entity_PaymentRequest::get($user_id);

		// пользователь не запрашивал оплату премиума
		if (is_null($payment_request)) {
			return;
		}

		// если запрос на оплату уже выполнен
		if ($payment_request->is_payed == 1) {
			return;
		}

		// обнуляем запрос на оплату для пользователя и отмечаем оплаченным
		// в случае если он вернётся в компанию в ближайший час, чтобы запрос не висел за ним
		Gateway_Db_CompanyData_PremiumPaymentRequestList::set($user_id, [
			"is_payed"     => 1,
			"requested_at" => 0,
			"updated_at"   => time(),
		]);

		// получаем адмниистратором с правами настройки компании
		$admin_list    = Domain_User_Action_Member_GetByPermissions::do([\CompassApp\Domain\Member\Entity\Permission::SPACE_SETTINGS]);
		$admin_id_list = array_column($admin_list, "user_id");

		// помечаем запросы на оплату от пользователя прочитанными для руководителей
		Gateway_Db_CompanyData_PremiumPaymentRequestMenu::setList($admin_id_list, $user_id, [
			"is_unread"  => 0,
			"updated_at" => time(),
		]);

		// получаем количество непрочитанных для руководителей
		$owner_id_list_by_unread = Domain_Premium_Entity_PaymentRequestMenu::getUnreadCountForOwnerList($admin_id_list);

		// для руководителей отправляем ws об удалении запроса оплаты от сотрудника
		foreach ($owner_id_list_by_unread as $unread_count => $owner_id_list) {
			Gateway_Bus_Sender::premiumPaymentRequestDeleted($unread_count, $owner_id_list);
		}
	}
}