<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Установить чат как прочитанный
 */
class Domain_Conversation_Action_SetAsRead {

	/**
	 * Установить чат как прочитанный
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param string $need_read_message_map
	 *
	 * @return array
	 * @throws ReturnFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_LeftMenuRowIsNotExist
	 */
	public static function do(int $user_id, string $conversation_map, string $need_read_message_map):array {

		Gateway_Db_CompanyConversation_Main::beginTransaction();

		// если чат пустой
		if (mb_strlen($need_read_message_map) < 1) {

			$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getForUpdate($user_id, $conversation_map);
			self::_rollbackAndThrowIfNotExistRowInLeftMenu($left_menu_row);

			// читаем сообщение
			$left_menu_row = Domain_Conversation_Feed_Action_ReadMessage::setConversationAsRead($user_id, $need_read_message_map, $left_menu_row);
			Gateway_Db_CompanyConversation_Main::commitTransaction();
			return [$left_menu_row, false];
		}

		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getForUpdate($user_id, $conversation_map);
		self::_rollbackAndThrowIfNotExistRowInLeftMenu($left_menu_row);

		$need_read_message_index = \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($need_read_message_map);
		$last_read_message_index = self::_getLastReadMessageIndex($left_menu_row);

		// если индекс пришедшего сообщения меньше индекса текущего прочитанного, то выходим
		if ($need_read_message_index < $last_read_message_index) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return [$left_menu_row, false];
		}

		// читаем сообщение
		$left_menu_row = Domain_Conversation_Feed_Action_ReadMessage::setConversationAsRead($user_id, $need_read_message_map, $left_menu_row);

		Gateway_Db_CompanyConversation_Main::commitTransaction();
		return [$left_menu_row, true];
	}

	/**
	 * Откатываем транзакцию и выбрасываем экзепшен если нет записи в левом меню
	 *
	 * @param array $left_menu_row
	 *
	 * @throws ReturnFatalException
	 * @throws cs_LeftMenuRowIsNotExist
	 */
	protected static function _rollbackAndThrowIfNotExistRowInLeftMenu(array $left_menu_row):void {

		// запись в левом меню не существует
		if (!isset($left_menu_row["user_id"])) {

			Gateway_Db_CompanyConversation_Main::rollback();
			throw new cs_LeftMenuRowIsNotExist();
		}
	}

	/**
	 * Gолучаем индекс последнего прочитанного сообщения
	 *
	 */
	protected static function _getLastReadMessageIndex(array $left_menu_row):int {

		$message_index = 0;
		if (mb_strlen($left_menu_row["last_read_message_map"]) > 0) {
			$message_index = \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($left_menu_row["last_read_message_map"]);
		}

		return $message_index;
	}
}