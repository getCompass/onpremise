<?php

namespace Compass\Company;

/**
 * Устанавливает данные текущего премиума для пользователя.
 */
class Domain_User_Action_Premium_SetStatus {

	/**
	 * Устанавливает данные текущего премиума для пользователя.
	 */
	public static function run(int $user_id, int $active_till, bool $need_block_if_inactive):void {

		// получаем все активные сессии пользователя
		$active_session_list = Gateway_Db_CompanyData_SessionActiveList::getByUser($user_id);

		// для каждый сессии вызываем обновление статуса премиума
		// не делаем просто запись экстры для всех, чтобы в будущем ничего не перезаписать точно
		foreach ($active_session_list as $active_session) {

			/** начало транзакции */
			Gateway_Db_CompanyData_SessionActiveList::beginTransaction();

			try {

				// получаем запись с блокировкой
				$session = Gateway_Db_CompanyData_SessionActiveList::getForUpdate($active_session->session_uniq);
			} catch (\cs_RowIsEmpty) {

				// не падаем, просто пропускаем —
				// может сессия успела инвалидироваться, пока мы тут крутились
				Gateway_Db_CompanyData_SessionActiveList::rollback();
				continue;
			}

			// обновляем запись
			$extra = Domain_User_Entity_ActiveSession_Extra::setPremiumInfo($session->extra, $active_till, $need_block_if_inactive);
			Gateway_Db_CompanyData_SessionActiveList::set($active_session->session_uniq, [
				"extra" => $extra,
			]);

			Gateway_Db_CompanyData_SessionActiveList::commitTransaction();
			/** конце транзакции */
		}

		// сбрасываем кэш сессий для пользователя
		Gateway_Bus_CompanyCache::clearSessionCacheByUserId($user_id);

		// обновляем запрос на оплату премиума
		self::_updatePremiumRequest($user_id);
	}

	/**
	 * Обновить запрос на оплату премиума
	 *
	 * @param int $user_id
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _updatePremiumRequest(int $user_id):void {

		// получаем руководителей компании
		$owner_list    = Domain_User_Action_Member_GetByPermissions::do([\CompassApp\Domain\Member\Entity\Permission::SPACE_SETTINGS]);
		$owner_id_list = array_column($owner_list, "user_id");

		// помечаем запросы на оплату прочитанными
		Gateway_Db_CompanyData_PremiumPaymentRequestMenu::setList($owner_id_list, $user_id, [
			"is_unread"  => 0,
			"updated_at" => time(),
		]);

		// помечаем запрос на оплату как оплаченный
		Gateway_Db_CompanyData_PremiumPaymentRequestList::set($user_id, [
			"is_payed"   => 1,
			"updated_at" => time(),
		]);

		// получаем количество непрочитанных для руководителей
		$owner_id_list_by_unread = Domain_Premium_Entity_PaymentRequestMenu::getUnreadCountForOwnerList($owner_id_list);

		// для руководителей отправляем ws о новой оплате сотрудника
		foreach ($owner_id_list_by_unread as $unread_count => $owner_id_list) {
			Gateway_Bus_Sender::premiumPaymentRequestPayed($owner_id_list, $unread_count);
		}
	}
}
