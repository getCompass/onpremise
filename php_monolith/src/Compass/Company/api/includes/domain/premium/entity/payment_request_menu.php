<?php

namespace Compass\Company;

/**
 * Сущность запросов оплаты премиума
 */
class Domain_Premium_Entity_PaymentRequestMenu {

	/**
	 * Создать записи в таблице меню запросов
	 *
	 * @param array $user_id_list
	 * @param int   $requested_by_user_id
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function createList(array $user_id_list, int $requested_by_user_id):array {

		$premium_payment_request_menu_list = Gateway_Db_CompanyData_PremiumPaymentRequestMenu::getList($user_id_list, $requested_by_user_id);

		$read_premium_payment_request_menu_list
			= array_filter($premium_payment_request_menu_list, fn(Struct_Db_CompanyData_PremiumPaymentRequestMenu $v) => $v->is_unread === 0);

		$existing_user_id_list      = array_column($premium_payment_request_menu_list, "user_id");
		$read_existing_user_id_list = array_column($read_premium_payment_request_menu_list, "user_id");

		$insert_user_id_list = array_diff($user_id_list, $existing_user_id_list);
		$update_user_id_list = array_intersect($user_id_list, $existing_user_id_list);
		$update_user_id_list = array_diff($update_user_id_list, $insert_user_id_list);

		self::_createOrUpdate($insert_user_id_list, $update_user_id_list, $requested_by_user_id);
		return $read_existing_user_id_list;
	}

	/**
	 * Получить количество непрочитанных запросов на оплату
	 *
	 * @param int $user_id
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getUnreadCount(int $user_id):int {

		return Gateway_Db_CompanyData_PremiumPaymentRequestMenu::getUnreadCount($user_id);
	}

	/**
	 * помечаем прочитанными для пользователя
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function setRead(int $user_id):void {

		$unread_count = self::getUnreadCount($user_id);

		$set = [
			"is_unread"  => 0,
			"updated_at" => time(),
		];

		Gateway_Db_CompanyData_PremiumPaymentRequestMenu::setRead($user_id, $set, $unread_count);
	}

	/**
	 * Добавить или обновить записи
	 *
	 * @param array $insert_user_id_list
	 * @param array $update_user_id_list
	 * @param int   $requested_by_user_id
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _createOrUpdate(array $insert_user_id_list, array $update_user_id_list, int $requested_by_user_id):void {

		$insert_list = [];

		foreach ($insert_user_id_list as $user_id) {

			$insert_list[] = new Struct_Db_CompanyData_PremiumPaymentRequestMenu(
				$user_id,
				$requested_by_user_id,
				1,
				time(),
				0
			);
		}

		// если что то надо вставить в таблицу - вставляем
		if (count($insert_list) > 0) {
			Gateway_Db_CompanyData_PremiumPaymentRequestMenu::insertList($insert_list);
		}

		$update_set = [
			"is_unread"  => 1,
			"updated_at" => time(),
		];

		// обновляем записи, если есть
		if (count($update_user_id_list) > 0) {
			Gateway_Db_CompanyData_PremiumPaymentRequestMenu::setList($update_user_id_list, $requested_by_user_id, $update_set);
		}
	}

	/**
	 * получаем количество непрочитанных для пользователей
	 */
	public static function getUnreadList(array $user_id_list):array {

		return Gateway_Db_CompanyData_PremiumPaymentRequestMenu::getUnreadList($user_id_list);
	}

	/**
	 * собираем количество непрочитанных заявок для руководителей
	 */
	public static function getUnreadCountForOwnerList(array $owner_id_list):array {

		$owner_id_list_by_unread = [];
		$unread_user_id_list     = [];

		// собираем пользователей, у кого имеются непрочитанные заявки
		$unread_list = Domain_Premium_Entity_PaymentRequestMenu::getUnreadList($owner_id_list);
		foreach ($unread_list as $unread) {

			$owner_id_list_by_unread[$unread["unread_count"]][] = $unread["user_id"];
			$unread_user_id_list[]                              = $unread["user_id"];
		}

		// получаем пользователей, у кого непрочитанные отсутствуют
		$not_exist_unread_user_id_list = array_diff($owner_id_list, $unread_user_id_list);
		$owner_id_list_by_unread[0]    = $not_exist_unread_user_id_list;

		return $owner_id_list_by_unread;
	}
}