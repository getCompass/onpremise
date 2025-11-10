<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\User\Main;

/**
 * Скрипт для пересчета количества непрочитанных личных чатов
 */
class Type_Script_Source_RecountSingleConversationUnreadForAllUserCompany extends Type_Script_CompanyUpdateTemplate {

	protected const _DB_KEY            = "company_data";
	protected const _TABLE_KEY         = "member_list";
	protected const _GET_BY_ROLE_INDEX = "npc_type.role.company_joined_at";

	/**
	 * Точка входа в скрипт.
	 *
	 * @param array $data
	 *
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 */
	public function exec(array $data):void {

		if ($this->_isDry()) {
			$this->_log("DRY_RUN - запуск без записи!!!");
		}

		$company_id = COMPANY_ID;
		$limit      = 500;
		$offset     = 0;

		do {

			// получаем пользователей состоящих в компании
			$member_list = self::getListByRoles([Member::ROLE_GUEST, Member::ROLE_MEMBER, Member::ROLE_ADMINISTRATOR,], $limit, $offset);

			// собираем user_id_list
			$user_id_list = array_column($member_list, "user_id");

			// проходимся по каждому пользователю для пересчета
			foreach ($user_id_list as $user_id) {

				$this->_log("Собираюсь обновить непрочитанные личные чаты у пользователя $user_id в компании $company_id\n");

				Gateway_Db_CompanyConversation_Main::beginTransaction();

				$total_unread_counters = Gateway_Db_CompanyConversation_UserLeftMenu::getTotalUnreadCounters($user_id);

				$total_message_unread_count             = $total_unread_counters["message_unread_count"] ?? 0;
				$total_conversation_unread_count        = $total_unread_counters["conversation_unread_count"] ?? 0;
				$total_single_conversation_unread_count = $total_unread_counters["single_conversation_unread_count"] ?? 0;

				$current_unread_counters                    = Gateway_Db_CompanyConversation_UserInbox::getOne($user_id);
				$current_unread_message_counter             = $current_unread_counters["message_unread_count"] ?? 0;
				$current_unread_conversation_counter        = $current_unread_counters["conversation_unread_count"] ?? 0;
				$current_unread_single_conversation_counter = $current_unread_counters["single_conversation_unread_count"] ?? 0;

				if ($this->_isDry()) {

					$this->_log("DRY_RUN - результат не будет сохранен!!!");

					$this->_log("У пользователя $user_id: \n 
					- $total_message_unread_count непрочитанных сообщений  
					- $total_conversation_unread_count непрочитанных всего чатов
					- $total_single_conversation_unread_count непрочитанных личных чатов (отсюда возьмём значение!!!) \n
					В счетчике сейчас: 
					- $current_unread_message_counter непрочитанных сообщений
					- $current_unread_conversation_counter непрочитанных всех чатов 
					- $current_unread_single_conversation_counter непрочитанных личных чатов (сюда запишем новое значение!!!) \n
					");

					Gateway_Db_CompanyConversation_Main::rollback();
					$this->_log("DRY_RUN - результат не сохранен!!!");
					continue;
				}

				Gateway_Db_CompanyConversation_UserInbox::set($user_id, [
					"single_conversation_unread_count" => (int) $total_single_conversation_unread_count,
					"updated_at"                       => time(),
				]);

				$this->_log("У пользователя $user_id: \n 
				- $current_unread_message_counter -> $total_message_unread_count непрочитанных сообщений
				- $current_unread_conversation_counter -> $total_conversation_unread_count непрочитанных всех чатов
				- $current_unread_single_conversation_counter -> $total_single_conversation_unread_count непрочитанных личных чатов \n
				");

				Gateway_Db_CompanyConversation_Main::commitTransaction();
			}

			// инкрементим offset
			$offset += $limit;
		} while (count($member_list) === $limit);
	}

	/**
	 * метод для получения списка участников по списку ролей
	 *
	 * @return \CompassApp\Domain\Member\Struct\Main[]
	 */
	public static function getListByRoles(array $roles, int $limit = 1, int $offset = 0):array {

		// INDEX=get_by_npc_type
		$query = "SELECT * FROM `?p` FORCE INDEX(`?p`) WHERE `npc_type` = ?i AND `role` IN (?a) ORDER BY `company_joined_at` ASC LIMIT ?i OFFSET ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, self::_GET_BY_ROLE_INDEX, Main::NPC_TYPE_HUMAN, $roles, $limit, $offset);

		$list = [];
		foreach ($rows as $row) {
			$list[$row["user_id"]] = self::_formatRow($row);
		}

		return $list;
	}

	/**
	 * преобразовываем массив в структуру
	 */
	protected static function _formatRow(array $row):\CompassApp\Domain\Member\Struct\Main {

		return new \CompassApp\Domain\Member\Struct\Main(
			$row["user_id"],
			$row["role"],
			$row["npc_type"],
			$row["permissions"],
			$row["created_at"],
			$row["updated_at"],
			$row["company_joined_at"],
			$row["left_at"],
			$row["full_name_updated_at"],
			$row["full_name"],
			$row["mbti_type"],
			$row["short_description"],
			$row["avatar_file_key"],
			$row["comment"],
			fromJson($row["extra"]),
		);
	}
}