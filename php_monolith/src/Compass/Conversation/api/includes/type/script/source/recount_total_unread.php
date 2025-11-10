<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * Скрипт для пересчета количества непрочитанных чатов
 */
class Type_Script_Source_RecountTotalUnread extends Type_Script_CompanyUpdateTemplate {

	/**
	 * Точка входа в скрипт.
	 *
	 * @param array $data
	 *
	 * @throws ReturnFatalException
	 * @throws BusFatalException
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

		$this->_log("Собираюсь обновить непрочитанные чаты у пользователя $user_id в компании $company_id\n");

		Gateway_Db_CompanyConversation_Main::beginTransaction();

		$total_unread_counters = Gateway_Db_CompanyConversation_UserLeftMenu::getTotalUnreadCounters($user_id);

		$total_message_unread_count       = $total_unread_counters["message_unread_count"] ?? 0;
		$total_conversation_unread_count  = $total_unread_counters["conversation_unread_count"] ?? 0;
		$single_conversation_unread_count = $total_unread_counters["single_conversation_unread_count"] ?? 0;

		$current_unread_counters             = Gateway_Db_CompanyConversation_UserInbox::getOne($user_id);
		$current_unread_message_counter      = $current_unread_counters["message_unread_count"] ?? 0;
		$current_unread_conversation_counter = $current_unread_counters["conversation_unread_count"] ?? 0;

		$this->_log("У пользователя $user_id: \n 
		- $total_message_unread_count непрочитанных сообщений \n 
		- $total_conversation_unread_count непрочитанных чатов. \n
		В счетчике: 
		- $current_unread_message_counter непрочитанных сообщений\n
		- $current_unread_conversation_counter непрочитанных чатов \n
		");

		if ($this->_isDry()) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return;
		}

		if ($total_message_unread_count < $total_conversation_unread_count) {
			$total_conversation_unread_count = $total_message_unread_count;
		}

		Gateway_Db_CompanyConversation_UserInbox::set($user_id, [
			"message_unread_count"             => (int) $total_message_unread_count,
			"conversation_unread_count"        => (int) $total_conversation_unread_count,
			"single_conversation_unread_count" => (int) $single_conversation_unread_count,
		]);

		Gateway_Db_CompanyConversation_Main::commitTransaction();
	}
}