<?php

namespace Compass\Thread;

/**
 * Действие для удаления Напоминания
 */
class Domain_Thread_Action_Message_RemoveRemind {

	/**
	 * выполняем
	 *
	 * @long
	 * @throws Domain_Remind_Exception_AlreadyDone
	 * @throws Domain_Remind_Exception_AlreadyRemoved
	 * @throws Domain_Remind_Exception_UserIsNotCreator
	 * @throws Domain_Thread_Exception_Message_NotAllowForRemind
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws cs_Message_IsDeleted
	 */
	public static function do(string $message_map, string $thread_map, array $meta_row, int $user_id):void {

		$block_id = \CompassApp\Pack\Message\Thread::getBlockId($message_map);

		Gateway_Db_CompanyThread_Main::beginTransaction();

		// получаем блок треда
		$block_row = Gateway_Db_CompanyThread_MessageBlock::getForUpdate($thread_map, $block_id);

		// получаем сообщение из блока
		$message = Type_Thread_Message_Block::getMessage($message_map, $block_row);

		// проверяем, что с сообщения можно убирать Напоминание
		try {
			Domain_Remind_Action_CheckMessageAllowedForRemind::do($message, $user_id);
		} catch (\Exception $e) {

			Gateway_Db_CompanyThread_Main::rollback();
			throw $e;
		}

		// проверяем, может сообщение уже не содержит Напоминания
		if (!Type_Thread_Message_Main::getHandler($message)::isAttachedRemind($message)) {

			Gateway_Db_CompanyThread_Main::rollback();
			throw new Domain_Remind_Exception_AlreadyRemoved("remind already removed");
		}

		// получаем флаг - создатель ли Напоминания наш пользователь
		$is_remind_creator = Type_Thread_Message_Main::getHandler($message)::isRemindCreator($message, $user_id);

		// получаем флаг может ли пользователь управлять сообщениями
		$is_manage = Type_Thread_Meta_Users::isCanManage($user_id, $meta_row["users"]);

		// если пользователь не создатель Напоминания и не админ
		if (!$is_remind_creator && !$is_manage) {

			Gateway_Db_CompanyThread_Main::rollback();
			throw new Domain_Remind_Exception_UserIsNotCreator("User is not creator or admin of group");
		}

		$remind_id = Type_Thread_Message_Main::getHandler($message)::getRemindId($message);

		// проверяем, может Напоминание уже выполнено
		$remind = Gateway_Db_CompanyData_RemindList::getOne($remind_id);
		if ($remind->is_done == 1) {

			Gateway_Db_CompanyThread_Main::rollback();
			throw new Domain_Remind_Exception_AlreadyDone("remind already done");
		}

		// удаляем запись из таблицы company_data . remind_list
		Gateway_Db_CompanyData_RemindList::delete($remind_id);

		// убираем из структуры сообщения данные Напоминания
		$message = Type_Thread_Message_Main::getHandler($message)::removeRemindData($message);

		// обновляем запись
		$block_row["data"][$message_map] = $message;
		Gateway_Db_CompanyThread_MessageBlock::set($thread_map, $block_id, [
			"data"       => $block_row["data"],
			"updated_at" => time(),
		]);
		Gateway_Db_CompanyThread_Main::commitTransaction();

		// отправляем ws-событие об удалении Напоминании
		$talking_user_list = Type_Thread_Meta_Users::getTalkingUserList($meta_row["users"]);
		Gateway_Bus_Sender::remindDeleted($remind_id, $message_map, $thread_map, $talking_user_list);
	}
}