<?php

namespace Compass\Company;

/**
 * Класс обработки сценариев событий Напоминаний.
 */
class Domain_Remind_Scenario_Event {

	/**
	 * отправляем сообщение-Напоминание в чат/тред, когда пришло время напомнить
	 *
	 * @throws \parseException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @long
	 */
	#[Type_Task_Attribute_Executor(Type_Event_Remind_SendRemindMessage::EVENT_TYPE, Struct_Event_Remind_SendRemindMessage::class)]
	public static function sendRemindMessage(Struct_Event_Remind_SendRemindMessage $event_data):Type_Task_Struct_Response {

		// достаём Напоминание из базы company_data . remind_list
		try {
			$remind = Gateway_Db_CompanyData_RemindList::getOne($event_data->remind_id);
		} catch (\cs_RowIsEmpty) { // завершаем задачу,так как напоминание было уже отменено
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
		}

		// если время Напоминания больше текущего, то отправляем на следующую итерацию, указывая когда нужно выполниться
		if ($remind->remind_at > time()) {
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, $remind->remind_at);
		}

		// если уже напомнили о сообщении, то завершаем задачу
		if ($remind->is_done == 1) {
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
		}

		// проверяем, не словил ли пользователь блокировку
		try {
			Type_Antispam_User::throwIfBlocked($remind->creator_user_id, Type_Antispam_User::REMIND_SEND_MESSAGE);
		} catch (\BaseFrame\Exception\Request\BlockException) {

			// в этом случае повторяем через минуту
			$expire = Type_Antispam_User::REMIND_SEND_MESSAGE["expire"];
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, time() + $expire);
		}

		// отправляем сообщение-Напоминание
		try {
			Domain_Remind_Action_SendRemindMessage::do($remind);
		} catch (\Exception|\Error $e) {

			$text = "Ошибка при отправке сообщения-Напоминания. Error: {$e->getMessage()}";
			Type_System_Admin::log("on-send-remind-message", $text);

			// если словили exception, то больше не пытаемся выполнить, чтобы не задублировались сообщения
			$text = "Не отправилось сообщение-Напоминание - проверьте лог файл \"on-send-remind-message\", чтобы узнать подробности";
			Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_KEY, $text);
		}

		// помечаем Напоминание как выполненное в таблице
		$set = [
			"is_done"    => 1,
			"updated_at" => time(),
		];
		Gateway_Db_CompanyData_RemindList::set($remind->remind_id, $set);

		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
	}
}
