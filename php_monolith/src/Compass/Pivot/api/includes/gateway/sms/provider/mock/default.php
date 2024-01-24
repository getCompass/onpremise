<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * дефолтный, наследуемый класс для моков
 */
class Gateway_Sms_Provider_Mock_Default {

	/**
	 * отправляем сообщение
	 *
	 * @throws \cs_CurlError
	 * @throws cs_SmsFailedRequestToProvider
	 */
	protected static function _sendMessage(string $phone_number, string $text):void {

		if (!inHtml($phone_number, "+")) {
			$phone_number = "+" . $phone_number;
		}

		// сохраняем сам текст смс
		Type_Sms_Mock::saveText($phone_number, $text);

		try {

			// проверяем что пользователь уже зарегистирован
			Domain_User_Entity_Phone::getUserIdByPhone($phone_number);

			// отправляем только на stage и ci-backend
			if (!ServerProvider::isOnPremise() && (isStageServer() || ServerProvider::isCi() || ServerProvider::isMaster())) {

				// отправляем также смс на бота
				Gateway_Sms_Provider_Userbot::sendSms($phone_number, $text);
			}
		} catch (cs_PhoneNumberNotFound) {
			// не делаем ничего
		}
	}
}