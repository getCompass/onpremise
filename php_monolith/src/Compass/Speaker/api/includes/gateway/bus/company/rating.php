<?php

namespace Compass\Speaker;

use BaseFrame\Exception\Request\ParamException;

/**
 * класс для работы с rating
 */
class Gateway_Bus_Company_Rating extends Gateway_Bus_Company_Main {

	public const CONVERSATION_MESSAGE = "conversation_message";
	public const THREAD_MESSAGE       = "thread_message";
	public const REACTION             = "reaction";
	public const FILE                 = "file";
	public const CALL                 = "call";
	public const VOICE                = "voice";

	/**
	 * инкремент статистики
	 *
	 * @param string $event
	 * @param int    $user_id
	 * @param int    $value optional
	 *
	 * @throws \parseException
	 */
	public static function inc(string $event, int $user_id, int $value = 1):void {

		// проверяем пришедшее значение value
		if ($value < 1) {
			return;
		}

		// формируем массив для запроса
		$ar_post = [
			"method"  => "rating.inc",
			"user_id" => $user_id,
			"event"   => $event,
			"inc"     => $value,
		];

		// отправляем задачу в очередь
		Gateway_Bus_Rabbit::sendMessage(self::_QUEUE_NAME, $ar_post);
	}

	/**
	 * функция для сохранения текущего состояния кеша в базу
	 */
	public static function forceSaveCache(int $company_id):void {

		$request       = new \CompanyGrpc\RatingForceSaveCacheRequestStruct([
			"company_id" => $company_id,
		]);
		$response_data = self::_doCallGrpc("RatingForceSaveCache", $request);
		$status        = $response_data[1];

		if ($status->code !== \Grpc\STATUS_OK) {
			self::_throwIfGrpcReturnNotOk($status);
		}
	}

	/**
	 * выдаем exception, если grpc не вернул ok
	 *
	 * @param object $status
	 *
	 * @throws ParamException
	 * @throws \busException
	 */
	protected static function _throwIfGrpcReturnNotOk(object $status):void {

		switch ($status->code) {

			case 400:

				$error_text = $status->details;
				throw new ParamException($error_text);

			default:
				throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}
}
