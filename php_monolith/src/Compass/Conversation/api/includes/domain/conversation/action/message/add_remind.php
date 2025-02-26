<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * Действие для добавления Напоминаний
 */
class Domain_Conversation_Action_Message_AddRemind {

	/**
	 * выполняем
	 *
	 * @throws Domain_Conversation_Exception_Message_NotAllowForUser
	 * @throws Domain_Remind_Exception_AlreadyExist
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public static function do(string $message_map, string $conversation_map, array $meta_row, string $comment, int $remind_at, int $user_id, int $remind_type = Domain_Remind_Entity_Remind::CONVERSATION_MESSAGE_TYPE):Struct_Db_CompanyData_Remind {

		// получаем dynamic-данные диалога
		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);

		// добавляем Напоминание
		/**
		 * @var Struct_Db_CompanyData_Remind                      $remind
		 * @var Struct_Db_CompanyConversation_ConversationDynamic $dynamic
		 */
		[$message, $remind, $dynamic] = self::_addRemind($user_id, $remind_type, $remind_at, $comment, $message_map, $conversation_map, $dynamic_row);

		// если к сообщению прикреплён тред, то удаляем родительское сообщение из кэша
		try {

			Gateway_Db_CompanyConversation_MessageThreadRel::getOneByMessageMap($conversation_map, $message_map);
			Type_Phphooker_Main::sendClearParentMessageCache($message_map);
		} catch (\cs_RowIsEmpty) {
			// ничего не делаем
		}

		// отправляем ws-событие о новом Напоминании
		$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($meta_row["users"]);
		Gateway_Bus_Sender::remindCreated(
			$remind->remind_id, $remind_at, $user_id, $message_map, $comment, $conversation_map, $dynamic->messages_updated_version, $talking_user_list
		);

		// пушим таск для отправки Напоминания в чат
		$event_data = [
			"remind_id" => $remind->remind_id,
		];
		Gateway_Bus_Event::pushTask(Type_Event_Remind_SendRemindMessage::EVENT_TYPE, $event_data, "php_company", need_work: $remind->remind_at);

		// инкрементим количество действий
		Domain_User_Action_IncActionCount::incConversationRemindCreated($user_id, $conversation_map);

		// получаем текст для сообщения в интерком
		$intercom_message_text = self::_prepareCreateRemindMessageToIntercom($message, $comment, $remind_at);

		// отправляем сообщение в интерком о том что создали напоминание
		Helper_Conversations::addMessageToIntercomQueueByText($intercom_message_text, $message, $meta_row["type"]);

		return $remind;
	}

	/**
	 * Добавляем Напоминание
	 *
	 * @throws Domain_Conversation_Exception_Message_NotAllowForUser
	 * @throws Domain_Remind_Exception_AlreadyExist
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @long
	 */
	protected static function _addRemind(int $user_id, int $remind_type, int $remind_at, string $comment, string $message_map, string $conversation_map, array $dynamic_row):array {

		$block_id = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);

		Gateway_Db_CompanyConversation_MessageBlock::beginTransaction();

		// получаем блок на обновление, получаем сообщение
		$block_row = Gateway_Db_CompanyConversation_MessageBlock::getForUpdate($conversation_map, $block_id);
		$message   = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);

		// проверяем, что пользователь может получить сообщение
		try {
			Domain_Conversation_Action_Message_CheckAllowMessage::do($user_id, $message, $dynamic_row);
		} catch (Domain_Conversation_Exception_Message_NotAllowForUser $e) {

			Gateway_Db_CompanyConversation_MessageBlock::rollback();
			throw $e;
		}

		// если сообщение уже содержит Напоминание
		if (Type_Conversation_Message_Main::getHandler($message)::isAttachedRemind($message)) {

			// если Напоминание ещё не истекло по времени или не выполнено
			$remind_id = Type_Conversation_Message_Main::getHandler($message)::getRemindId($message);
			$remind    = Gateway_Db_CompanyData_RemindList::getOne($remind_id);
			if (!Type_Conversation_Message_Main::getHandler($message)::isRemindExpires($message) && $remind->is_done != 1) {

				Gateway_Db_CompanyConversation_MessageBlock::rollback();
				throw new Domain_Remind_Exception_AlreadyExist("remind already set in message_block_remind_list");
			}
		}

		// проверяем, что к сообщению можно добавлять Напоминание
		try {

			Domain_Remind_Action_CheckMessageAllowedForRemind::do($message, $user_id);
		} catch (\Exception $e) {

			Gateway_Db_CompanyConversation_MessageBlock::rollback();
			throw $e;
		}

		// добавляем Напоминание в company_data . remind_list для будущего триггера
		$data   = Domain_Remind_Entity_Remind::initData($comment);
		$remind = Gateway_Db_CompanyData_RemindList::insert($remind_type, $remind_at, $user_id, $message_map, $data);

		// добавляем Напоминание в message_block_remind_list для связи Напоминания и блока сообщения
		$message = Type_Conversation_Message_Main::getHandler($message)::addRemindData($message, $remind->remind_id, $remind->remind_at, $user_id, $comment);
		Domain_Conversation_Entity_Message_Block_Main::updateDataInMessageBlock($conversation_map, $message_map, $block_row, $block_id, $message);

		$dynamic = Gateway_Db_CompanyConversation_ConversationDynamic::getForUpdate($conversation_map);

		$dynamic->messages_updated_version = $dynamic->messages_updated_version + 1;
		$dynamic->messages_updated_at      = time();
		$dynamic->updated_at               = time();

		// обновляем временную метку и версию обновления сообщений в диалоге
		$set = [
			"messages_updated_version" => $dynamic->messages_updated_version,
			"messages_updated_at"      => $dynamic->messages_updated_at,
			"updated_at"               => $dynamic->updated_at,
		];
		Domain_Conversation_Entity_Dynamic::set($conversation_map, $set);

		Gateway_Db_CompanyConversation_MessageBlock::commitTransaction();

		return [$message, $remind, $dynamic];
	}

	/**
	 * Формируем нужный формат сообщения для отправки в интерком
	 */
	protected static function _prepareCreateRemindMessageToIntercom(array $message, string $comment, int $time):string {

		$message_text = Type_Conversation_Message_Main::getHandler($message)::getText($message);
		$system_info  = Gateway_Socket_Intercom::SYSTEM_CREATE_REMIND_MESSAGE;

		$date_text         = date("d.m.y", $time);
		$time_text         = date("H:i", $time);
		$time_text_message = "{$date_text} в $time_text МСК";

		$comment_for_remind = mb_strlen($comment) > 0 ? "\n\n<b>Комментарий к напоминанию</b>\n" : "";

		return "{$system_info}\n\n<b>Сообщение</b>\n{$message_text}{$comment_for_remind}{$comment}\n\n<b>Сработает</b>\n{$time_text_message}";
	}
}