<?php

namespace Compass\Thread;

/**
 * Действие для добавления Напоминаний
 */
class Domain_Thread_Action_Message_AddRemind {

	/**
	 * выполняем
	 *
	 * @return Struct_Db_CompanyData_Remind
	 * @throws Domain_Remind_Exception_AlreadyExist
	 * @throws Domain_Thread_Exception_Message_NotAllowForRemind
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws cs_Message_IsDeleted
	 * @long
	 */
	public static function do(string $message_map, string $thread_map, array $meta_row, string $comment, int $remind_at, int $user_id):Struct_Db_CompanyData_Remind {

		Gateway_Db_CompanyThread_Main::beginTransaction();

		// получаем блок на обновление и сообщение из блока
		$block_id  = \CompassApp\Pack\Message\Thread::getBlockId($message_map);
		$block_row = Gateway_Db_CompanyThread_MessageBlock::getForUpdate($thread_map, $block_id);

		$message = Type_Thread_Message_Block::getMessage($message_map, $block_row);

		// проверяем, что к сообщению можно добавлять Напоминание
		try {
			Domain_Remind_Action_CheckMessageAllowedForRemind::do($message, $user_id);
		} catch (\Exception $e) {

			Gateway_Db_CompanyThread_Main::rollback();
			throw $e;
		}

		// если сообщение уже содержит Напоминание
		if (Type_Thread_Message_Main::getHandler($message)::isAttachedRemind($message)) {

			$remind_id = Type_Thread_Message_Main::getHandler($message)::getRemindId($message);
			$remind    = Gateway_Db_CompanyData_RemindList::getOne($remind_id);
			if (!Type_Thread_Message_Main::getHandler($message)::isRemindExpires($message) && $remind->is_done != 1) {

				Gateway_Db_CompanyThread_Main::rollback();
				throw new Domain_Remind_Exception_AlreadyExist("remind already set in message_block_remind_list");
			}
		}

		// добавляем Напоминание в company_data . remind_list для будущего триггера
		$remind_type = Domain_Remind_Entity_Remind::THREAD_MESSAGE_TYPE;
		$data        = Domain_Remind_Entity_Remind::initData($comment);
		$remind      = Gateway_Db_CompanyData_RemindList::insert($remind_type, $remind_at, $user_id, $message_map, $data);

		// добавляем Напоминание в структуру сообщения и обновляем сообщение в блоке
		$message = Type_Thread_Message_Main::getHandler($message)::addRemindData($message, $remind->remind_id, $remind->remind_at, $user_id, $comment);

		$block_row["data"][$message_map] = $message;
		Gateway_Db_CompanyThread_MessageBlock::set($thread_map, $block_id, [
			"data"       => $block_row["data"],
			"updated_at" => time(),
		]);

		Gateway_Db_CompanyThread_Main::commitTransaction();

		// обновляем метку тредов для диалога
		$parent_conversation_map = Type_Thread_SourceParentRel::getMap($meta_row["source_parent_rel"]);

		// отправляем ws-событие о новом Напоминании
		$talking_user_list = Type_Thread_Meta_Users::getTalkingUserList($meta_row["users"]);
		Gateway_Bus_Sender::remindCreated($remind->remind_id, $remind_at, $user_id, $message_map, $comment, $thread_map, $talking_user_list);

		// пушим таск для отправки Напоминания в тред
		$event_data = [
			"remind_id" => $remind->remind_id,
		];
		Gateway_Bus_Event::pushTask(Type_Event_Remind_SendRemindMessage::EVENT_TYPE, $event_data, "php_company", need_work: $remind->remind_at);

		// инкрементим количество действий
		Domain_User_Action_IncActionCount::incThreadRemindCreated($user_id, $parent_conversation_map);

		return $remind;
	}
}