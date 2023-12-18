<?php

declare(strict_types = 1);

namespace Compass\Thread;

/**
 * Action для создания системного сообщения при смене статуса заявки увольнения
 */
class Domain_Thread_Action_OnDismissalRequestStatusChanged {

	protected const _DISMISSAL_REQUEST_APPROVED          = "approved";     // увольнение одобрено
	protected const _DISMISSAL_REQUEST_REJECTED          = "rejected";     // заявка отклонена
	protected const _DISMISSAL_REQUEST_USER_LEFT_COMPANY = "left_company"; // пользователь покинул компанию

	// список доступных статусов заявок на увольнение
	protected const _ALLOWED_STATUS_LIST = [
		self::_DISMISSAL_REQUEST_APPROVED,
		self::_DISMISSAL_REQUEST_REJECTED,
		self::_DISMISSAL_REQUEST_USER_LEFT_COMPANY,
	];

	/**
	 * Выполняем action
	 *
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \parseException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ThreadIsReadOnly
	 */
	public static function do(string $thread_map, string $new_status, int $user_id, int $candidate_user_id):void {

		// формируем системное сообщение в зависимости от нового статуса заявки
		if (!in_array($new_status, self::_ALLOWED_STATUS_LIST, true)) {
			return;
		}

		// класс обработчик сообщения из треда
		$thread_message_class = Type_Thread_Message_Main::getLastVersionHandler();

		// формируем системное сообщение в зависимости от нового статуса заявки
		$message_list = match ($new_status) {

			self::_DISMISSAL_REQUEST_APPROVED => [
				$thread_message_class::makeSystemApproveDismissalRequest($user_id),
				$thread_message_class::makeSystemDismissalRequestOnUserLeftCompany($candidate_user_id),
			],
			self::_DISMISSAL_REQUEST_REJECTED => [$thread_message_class::makeSystemRejectDismissalRequest($user_id)],
			self::_DISMISSAL_REQUEST_USER_LEFT_COMPANY => [$thread_message_class::makeSystemDismissalRequestOnUserLeftCompany($candidate_user_id)],
		};

		// получаем мету треда
		$thread_meta_row = Type_Thread_Meta::getOne($thread_map);

		// добавляем в тред системное сообщение
		Domain_Thread_Action_Message_AddList::do($thread_map, $thread_meta_row, $message_list);
	}
}