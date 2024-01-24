<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для мокинга работы с API-шлюзом провайдера sms-agent.ru
 */
class Gateway_Sms_Provider_Mock_SmsAgent extends Gateway_Sms_Provider_Mock_Default {

	/**
	 * мокаем отправку реквеста
	 *
	 * @return Struct_Gateway_Sms_Provider_Response
	 * @throws cs_SmsFailedRequestToProvider
	 * @throws ParseFatalException
	 * @throws \cs_CurlError
	 */
	public static function mockRequest(string $action, array $ar_post):Struct_Gateway_Sms_Provider_Response {

		// в зависимости от действия
		switch ($action) {

			case "send":

				// генерируем случаный sms_id
				$data = mt_rand(1000000, 9999999);

				// отправляем сообщение
				self::_sendMessage($ar_post["to"], $ar_post["text"]);

				break;

			case "status":

				// статус отправки – по умолчанию 2
				$default_response = new Struct_Gateway_Sms_Provider_Response(200, timeMs(), 2);

				// получаем мокнутый статус
				$data = Type_Sms_Mock::getSmsStatus($ar_post["id"], $default_response)->body;

				break;

			case "balans":

				// баланс – всегда 5 000 рублей
				$data = 5000;

				break;

			default:

				throw new ParseFatalException(__METHOD__ . ": unexpected action");
		}

		return new Struct_Gateway_Sms_Provider_Response(200, timeMs(), $data);
	}

	/**
	 * мокаем смс статус
	 *
	 * @throws ParseFatalException
	 * @throws \cs_CurlError
	 */
	public static function mockSmsStatus(string $sms_id, int $status):void {

		// если прислали кривой статус
		if (!in_array($status, [
			Gateway_Sms_Provider_Abstract::STATUS_IN_PROGRESS,
			Gateway_Sms_Provider_Abstract::STATUS_DELIVERED,
			Gateway_Sms_Provider_Abstract::STATUS_NOT_DELIVERED,
		])) {
			throw new ParseFatalException("unexpected status");
		}

		$sms_agent_internal_status = match ($status) {
			Gateway_Sms_Provider_Abstract::STATUS_IN_PROGRESS   => 0,
			Gateway_Sms_Provider_Abstract::STATUS_DELIVERED     => 2,
			Gateway_Sms_Provider_Abstract::STATUS_NOT_DELIVERED => 3,
		};

		Type_Sms_Mock::saveSmsStatus($sms_id, new Struct_Gateway_Sms_Provider_Response(200, timeMs(), $sms_agent_internal_status));
	}
}