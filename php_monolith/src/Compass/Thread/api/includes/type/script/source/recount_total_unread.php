<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Скрипт для пересчета количества непрочитанных тредов
 */
class Type_Script_Source_RecountTotalUnread extends Type_Script_CompanyUpdateTemplate {

	/**
	 * Точка входа в скрипт.
	 *
	 * @param array $data
	 *
	 * @throws ReturnFatalException
	 */
	public function exec(array $data):void {

		$user_id    = $data["user_id"];
		$company_id = COMPANY_ID;

		try {
			Gateway_Bus_CompanyCache::getMember($user_id);
		} catch (\cs_RowIsEmpty) {
			$this->_log("Пользователь $user_id не найден в компании $company_id");
			return;
		}

		$this->_log("Собираюсь обновить непрочитанные треды у пользователя $user_id в компании $company_id\n");

		Gateway_Db_CompanyThread_Main::beginTransaction();

		$total_unread_counters      = Gateway_Db_CompanyThread_UserThreadMenu::getTotalUnreadCounters($user_id);
		$total_message_unread_count = $total_unread_counters["message_unread_count"] ?? 0;
		$total_thread_unread_count  = $total_unread_counters["thread_unread_count"] ?? 0;

		$current_unread_counters        = Gateway_Db_CompanyThread_UserInbox::getOne($user_id);
		$current_unread_message_counter = $current_unread_counters["message_unread_count"] ?? 0;
		$current_unread_thread_counter  = $current_unread_counters["thread_unread_count"] ?? 0;

		$this->_log("У пользователя $user_id: \n 
		- $total_message_unread_count непрочитанных сообщений \n 
		- $total_thread_unread_count непрочитанных тредов. \n
		В счетчике: 
		- $current_unread_message_counter непрочитанных сообщений\n
		- $current_unread_thread_counter непрочитанных тредов \n
		");

		if ($this->_isDry()) {

			Gateway_Db_CompanyThread_Main::rollback();
			return;
		}

		if ($total_message_unread_count < $total_thread_unread_count) {
			$total_thread_unread_count = $total_message_unread_count;
		}

		Gateway_Db_CompanyThread_UserInbox::set($user_id, [
			"message_unread_count" => (int) $total_message_unread_count,
			"thread_unread_count"  => (int) $total_thread_unread_count,
		]);

		Gateway_Db_CompanyThread_Main::commitTransaction();
	}
}