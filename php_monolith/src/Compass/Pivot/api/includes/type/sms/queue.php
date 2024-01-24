<?php

namespace Compass\Pivot;

/**
 * Класс для основной работы по отправке смс
 */
class Type_Sms_Queue {

	/**
	 * этапы отправки смс
	 */
	public const STAGE_NEED_SEND_SMS         = 0; // на этом этапе выбираем провайдера и отправляем смс
	public const STAGE_NEED_CHECK_STATUS_SMS = 1; // на этом этапе отслеживаем статус отправки смс, в конце пишем в историю и удаляем задачу

	/**
	 * кол-во секунд в течение которых смс должна быть помечана статусом отправлено со стороны провайдера
	 * иначе считаем ее проваленной
	 */
	protected const _SEND_SMS_EXPIRE_TASK_AFTER = 60;

	/**
	 * Добавляем задачу на отправку смс
	 *
	 * @throws \queryException
	 */
	public static function send(string $phone_number, string $text, int $story_type, string $story_id, string $sms_id = "", array $excluded_provider_list = [], string $resend_for_sms_id = "", string $force_send_via_provider_id = ""):string {

		// если не прислали sms_id, то генерируем сами
		if ($sms_id === "") {
			$sms_id = generateUUID();
		}

		if (in_array($phone_number, [
			IOS_TEST_PHONE,
			IOS_TEST_PHONE2,
			IOS_TEST_PHONE3,
			IOS_TEST_PHONE4,
			ELECTRON_TEST_PHONE,
			ANDROID_TEST_PHONE,
		])) {

			return $sms_id;
		}

		self::_doSaveTask(
			$phone_number,
			$text,
			$sms_id,
			$excluded_provider_list,
			$resend_for_sms_id,
			$force_send_via_provider_id,
			$story_type,
			$story_id
		);

		return $sms_id;
	}

	/**
	 * Добавляем задачу на переотправку смс
	 *
	 * @throws \queryException
	 */
	public static function resend(string $phone_number, string $text, string $resend_for_sms_id, int $story_type, string $story_id, string $sms_id = ""):string {

		// получаем историю отправки этого смс
		$send_history_list = Gateway_Db_PivotHistoryLogs_SendHistory::getBySmsId($resend_for_sms_id);

		// получаем все используемые provider_id, чтобы исключить их из выборки
		$failed_provider_id_list = [];
		foreach ($send_history_list as $send_history) {

			if ($send_history->is_success == 0 && $send_history->provider_id !== "") {
				$failed_provider_id_list[] = $send_history->provider_id;
			}
		}

		// добавляем задачу на отправку по новой
		return self::send(
			$phone_number,
			$text,
			$story_type,
			$story_id,
			$sms_id,
			$failed_provider_id_list,
			$resend_for_sms_id
		);
	}

	/**
	 * Сохраним задачу в базу
	 * @throws \queryException
	 */
	protected static function _doSaveTask(string $phone_number,
							  string $text,
							  string $sms_id = "",
							  array  $excluded_provider_list = [],
							  string $resend_for_sms_id = "",
							  string $force_send_via_provider_id = "",
							  int    $story_type = 0,
							  string $story_map = ""):void {

		// инициализируем и собираем extra
		$extra = Type_Sms_Queue_Extra::init($excluded_provider_list, $resend_for_sms_id, $story_type, $story_map);

		// добавляем задачу в базу
		Gateway_Db_PivotSmsService_SendQueue::insert(
			$phone_number,
			$text,
			$sms_id,
			self::STAGE_NEED_SEND_SMS,
			time(),
			time() + self::_SEND_SMS_EXPIRE_TASK_AFTER,
			$force_send_via_provider_id,
			$extra
		);
	}
}