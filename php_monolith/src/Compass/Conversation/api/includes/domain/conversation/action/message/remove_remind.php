<?php

namespace Compass\Conversation;

/**
 * Действие для удаления Напоминания
 */
class Domain_Conversation_Action_Message_RemoveRemind {

	/**
	 * выполняем
	 *
	 * @throws Domain_Conversation_Exception_Message_NotAllowForUser
	 * @throws Domain_Remind_Exception_AlreadyDone
	 * @throws Domain_Remind_Exception_AlreadyRemoved
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws cs_UserIsNotAdmin
	 */
	public static function do(string $message_map, string $conversation_map, array $meta_row, int $user_id, int $role, int $permissions):void {

		// получаем dynamic-данные диалога
		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);

		// убираем Напоминание
		/** @var Struct_Db_CompanyConversation_ConversationDynamic $dynamic */
		[$remind_id, $dynamic] = self::_removeRemind($user_id, $message_map, $conversation_map, $meta_row, $dynamic_row, $role, $permissions);

		// если к сообщению прикреплён тред, то удаляем родительское сообщение из кэша
		try {

			Gateway_Db_CompanyConversation_MessageThreadRel::getOneByMessageMap($conversation_map, $message_map);
			Type_Phphooker_Main::sendClearParentMessageCache($message_map);
		} catch (\cs_RowIsEmpty) {
			// ничего не делаем
		}

		// отправляем ws-событие об удалении Напоминания
		$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($meta_row["users"]);
		Gateway_Bus_Sender::remindDeleted($remind_id, $message_map, $conversation_map, $dynamic->messages_updated_version, $talking_user_list);
	}

	/**
	 * убираем Напоминание
	 *
	 * @long
	 * @throws Domain_Conversation_Exception_Message_NotAllowForUser
	 * @throws Domain_Remind_Exception_AlreadyDone
	 * @throws Domain_Remind_Exception_AlreadyRemoved
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws cs_UserIsNotAdmin
	 */
	protected static function _removeRemind(int $user_id, string $message_map, string $conversation_map, array $meta_row, array $dynamic_row, int $role, int $permissions):array {

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

		// проверяем, что у сообщения можно удалить Напоминание
		try {
			Domain_Remind_Action_CheckMessageAllowedForRemind::do($message, $user_id);
		} catch (\Exception $e) {

			Gateway_Db_CompanyConversation_MessageBlock::rollback();
			throw $e;
		}

		// проверяем, создатель ли Напоминания наш пользователь
		$is_remind_creator = Type_Conversation_Message_Main::getHandler($message)::isRemindCreator($message, $user_id);

		// если не создатель Напоминания и диалог групповой и пользователь не админ группы, то ругаемся
		if (!$is_remind_creator && Type_Conversation_Meta::isSubtypeOfGroup((int) $meta_row["type"]) &&
			!Type_Conversation_Meta_Users::isGroupAdmin($user_id, $meta_row["users"])) {

			Gateway_Db_CompanyConversation_MessageBlock::rollback();
			throw new cs_UserIsNotAdmin();
		}

		// если сообщение уже не содержит Напоминание, то есть ранее уже удалили
		if (!Type_Conversation_Message_Main::getHandler($message)::isAttachedRemind($message)) {

			Gateway_Db_CompanyConversation_MessageBlock::rollback();
			throw new Domain_Remind_Exception_AlreadyRemoved("remind already removed");
		}

		$remind_id = Type_Conversation_Message_Main::getHandler($message)::getRemindId($message);

		// проверяем, может Напоминание уже выполнено
		$remind = Gateway_Db_CompanyData_RemindList::getOne($remind_id);
		if ($remind->is_done == 1) {

			Gateway_Db_CompanyConversation_MessageBlock::rollback();
			throw new Domain_Remind_Exception_AlreadyDone("remind already done");
		}

		// удаляем запись из таблицы company_data . remind_list
		Gateway_Db_CompanyData_RemindList::delete($remind_id);

		// удаляем из структуры данные Напоминания
		$message = Type_Conversation_Message_Main::getHandler($message)::removeRemindData($message);

		// обновляем сообщение в блоке
		Domain_Conversation_Entity_Message_Block_Main::updateDataInMessageBlock($conversation_map, $message_map, $block_row, $block_id, $message);

		// обновляем временную метку и версию обновления сообщений в диалоге
		$dynamic                           = Gateway_Db_CompanyConversation_ConversationDynamic::getForUpdate($conversation_map);
		$dynamic->messages_updated_at      = time();
		$dynamic->messages_updated_version = $dynamic->messages_updated_version + 1;
		$dynamic->updated_at               = time();

		$set = [
			"messages_updated_at"      => $dynamic->messages_updated_at,
			"messages_updated_version" => $dynamic->messages_updated_version,
			"updated_at"               => $dynamic->updated_at,
		];
		Domain_Conversation_Entity_Dynamic::set($conversation_map, $set);

		Gateway_Db_CompanyConversation_MessageBlock::commitTransaction();

		return [$remind_id, $dynamic];
	}
}