<?php

declare(strict_types = 1);

namespace Compass\Thread;

/**
 * Action для создания системного сообщения при смене статуса заявки найма
 */
class Domain_Thread_Action_OnHiringRequestStatusChanged {

	protected const _HIRING_REQUEST_REJECTED                 = "rejected";                 // заявка отклонена (например, руководителем)
	protected const _HIRING_REQUEST_REVOKED                  = "revoked";                  // пользователь сам отозвал свою заявку
	protected const _HIRING_REQUEST_CONFIRMED                = "confirmed";                // заявка одобрена
	protected const _HIRING_REQUEST_CONFIRMED_POSTMODERATION = "confirmed_postmoderation"; // заявка одобрена
	protected const _HIRING_REQUEST_DISMISSED                = "dismissed";                // увольнение одобрено
	protected const _HIRING_REQUEST_USER_LEFT_COMPANY        = "left_company";             // пользователь покинул компанию

	// список доступных статусов заявок найма
	protected const _ALLOWED_STATUS_LIST = [
		self::_HIRING_REQUEST_REJECTED,
		self::_HIRING_REQUEST_REVOKED,
		self::_HIRING_REQUEST_CONFIRMED,
		self::_HIRING_REQUEST_CONFIRMED_POSTMODERATION,
		self::_HIRING_REQUEST_DISMISSED,
		self::_HIRING_REQUEST_USER_LEFT_COMPANY,
	];

	/**
	 * Выполняем action
	 *
	 * @param string $thread_map
	 * @param string $new_status
	 * @param int    $user_id
	 * @param int    $candidate_user_id
	 * @param array  $candidate_info
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
	public static function do(string $thread_map, string $new_status, int $user_id, int $candidate_user_id, array $candidate_info):void {

		// проверяем, что пришел корректный статус заявки
		if (!in_array($new_status, self::_ALLOWED_STATUS_LIST, true)) {
			return;
		}

		// формируем системное сообщение в зависимости от нового статуса заявки
		$message_list = self::_getMessageListByStatus($new_status, $user_id, $candidate_user_id);

		// если сообщения слать не нужно
		if (count($message_list) < 1) {
			return;
		}

		// подготавливаем дополнительные данные (exclude_follow_user_id_list - список пользователей которые мы не подписываем на тред)
		$additional_data = [
			"full_name"                   => $candidate_info["full_name"] ?? "",
			"exclude_follow_user_id_list" => [$candidate_user_id],
		];

		// т.к сообщения от разных пользователей, шлем по одному чтобы был разный отправитель (все их в массиве максимум два)
		foreach ($message_list as $message) {

			// получаем мету треда
			$thread_meta_row = Type_Thread_Meta::getOne($thread_map);

			// добавляем в тред системное сообщение
			Domain_Thread_Action_Message_AddList::do(
				$thread_map,
				$thread_meta_row,
				[$message],
				additional_data: $additional_data
			);
		}
	}

	/**
	 * Формируем системное сообщение в зависимости от нового статуса заявки
	 *
	 * @param string $new_status
	 * @param int    $user_id
	 * @param int    $candidate_user_id
	 *
	 * @return array
	 * @throws \parseException
	 */
	protected static function _getMessageListByStatus(string $new_status, int $user_id, int $candidate_user_id):array {

		// класс обработчик сообщения из треда
		$thread_message_class = Type_Thread_Message_Main::getLastVersionHandler();

		return match ($new_status) {

			self::_HIRING_REQUEST_CONFIRMED => [
			],
			self::_HIRING_REQUEST_CONFIRMED_POSTMODERATION => [
				$thread_message_class::makeSystemConfirmHiringRequest($user_id),
				$thread_message_class::makeSystemHiringRequestOnCandidateJoinCompany($candidate_user_id),
			],
			self::_HIRING_REQUEST_REJECTED => [
				$thread_message_class::makeSystemRejectHiringRequest($user_id),
			],
			self::_HIRING_REQUEST_REVOKED => [
				$thread_message_class::makeSystemRevokeHiringRequestSelf($user_id),
			],
			self::_HIRING_REQUEST_DISMISSED, => [
				$thread_message_class::makeSystemDismissHiringRequest($user_id),
				$thread_message_class::makeSystemHiringRequestOnUserLeftCompany($candidate_user_id),
			],
			self::_HIRING_REQUEST_USER_LEFT_COMPANY => [$thread_message_class::makeSystemHiringRequestOnUserLeftCompany($candidate_user_id)],
		};
	}
}