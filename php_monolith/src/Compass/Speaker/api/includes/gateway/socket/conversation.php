<?php

declare(strict_types = 1);

namespace Compass\Speaker;

/**
 * Класс, описывающий методы взаимодействия с модулем php_conversation.
 */
class Gateway_Socket_Conversation extends Gateway_Socket_Default {

	/**
	 * Метод для проверки можем ли звонить пользователю
	 *
	 * @throws \parseException
	 * @throws \returnException
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws cs_Call_ConversationNotExist
	 * @throws cs_Call_MemberIsDisabled
	 * @throws Domain_Member_Exception_AttemptInitialCall
	 */
	public static function checkIsAllowedForCall(int $user_id, int $opponent_user_id, int $method_version):string {

		$request = [
			"user_id"          => $user_id,
			"opponent_user_id" => $opponent_user_id,
			"method_version"   => $method_version,
		];

		$response = self::doCall("conversations.checkIsAllowedForCall", $request, $user_id);

		if ($response["status"] !== "ok") {

			// сокет-запрос вернул код ошибки?
			if (!isset($response["response"]["error_code"])) {
				throw new \returnException(__CLASS__ . ": request return call not \"ok\"");
			}

			// выбрасываем исключения на основании error_code
			self::_throwIfErrorCode($response);

			throw new \parseException("socket method failed");
		}

		return $response["response"]["conversation_map"];
	}

	/**
	 * метод для, чтобы отправить сообщение о звонке
	 *
	 * @throws \parseException
	 */
	public static function addCallMessage(string $conversation_map, string $call_map, int $user_id, string $platform):bool {

		$request = [
			"conversation_map" => $conversation_map,
			"call_map"         => $call_map,
			"platform"         => $platform,
		];

		$response = self::doCall("conversations.addCallMessage", $request, $user_id);

		if ($response["status"] !== "ok") {

			// сокет-запрос вернул код ошибки?
			if (!isset($response["response"]["error_code"])) {
				throw new \returnException(__CLASS__ . ": request return call not \"ok\"");
			}

			// выбрасываем исключения на основании error_code
			self::_throwIfErrorCode($response);

			throw new \parseException("socket method failed");
		}

		return true;
	}

	/**
	 * метод для чтения сообщения
	 *
	 * @param int    $user_id
	 * @param string $conversation_key
	 * @param string $user_local_date
	 * @param string $user_local_time
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function doReadMessage(int $user_id, string $conversation_key, string $user_local_date, string $user_local_time):void {

		$request = [
			"user_id"          => $user_id,
			"conversation_key" => $conversation_key,
			"user_local_date"  => $user_local_date,
			"user_local_time"  => $user_local_time,
		];

		$response = self::doCall("conversations.doRead", $request, $user_id);

		if ($response["status"] !== "ok") {

			// сокет-запрос вернул код ошибки?
			if (!isset($response["response"]["error_code"])) {
				throw new \returnException(__CLASS__ . ": request return call not \"ok\"");
			}

			throw new \parseException("socket method failed");
		}
	}

	// получаем подпись из массива параметров
	public static function doCall(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketCompanyUrl("conversation");
		return self::_doCall($url, $method, $params, $user_id);
	}

	/**
	 * Выбрасываем исключения на основании error_code
	 *
	 * @throws Domain_Member_Exception_AttemptInitialCall
	 * @throws cs_Call_ConversationNotExist
	 * @throws cs_Call_MemberIsDisabled
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 */
	protected static function _throwIfErrorCode(array $response):void {

		switch ($response["response"]["error_code"]) {

			case 10026:
				throw new cs_Call_ConversationNotExist();

			case 10021:
				throw new cs_Call_MemberIsDisabled();

			case 10024:
				throw new Domain_Member_Exception_AttemptInitialCall(Domain_Member_Exception_AttemptInitialCall::OPPONENT_TRAIT_SPACE_RESIDENT);

			case 10025:
				throw new Domain_Member_Exception_AttemptInitialCall(Domain_Member_Exception_AttemptInitialCall::OPPONENT_TRAIT_GUEST);

			case 10027:
				throw new Domain_Member_Exception_AttemptInitialCall(Domain_Member_Exception_AttemptInitialCall::OPPONENT_TRAIT_BOT);

			case 10022:
				throw new Domain_Member_Exception_ActionNotAllowed("Action not allowed");
		}
	}
}