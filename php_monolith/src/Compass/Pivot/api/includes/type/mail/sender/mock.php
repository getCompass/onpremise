<?php

namespace Compass\Pivot;

/**
 * Вспомогательный класс для мокинга отправки писем
 */
class Type_Mail_Sender_Mock extends Type_Mail_Sender_AbstractProvider {

	/**
	 * отправляем письмо
	 *
	 * @return bool
	 * @throws \cs_CurlError
	 */
	public function send(string $subject, string $body_html_content, string $receiver_address, string $receiver_name = ""):bool {

		self::saveText($receiver_address, $subject, $body_html_content);

		return true;
	}

	/**
	 * мокаем текст письма
	 *
	 * @throws \cs_CurlError
	 */
	public static function saveText(string $mail, string $subject, string $content):void {

		Type_Mock_Service::makeRequest(self::_getKeyForMockedText($mail), ["subject" => $subject, "content" => $content]);
	}

	/**
	 * получаем последнее замоканное письмо
	 *
	 * @throws \cs_CurlError
	 * @throws cs_MockedDataIsNotFound
	 */
	public static function getLastMail(string $mail):array {

		$key             = self::_getKeyForMockedText($mail);
		$mocked_response = self::_getMockedResponse($key);

		$response = fromJson($mocked_response["response"]["response"]);

		return [$response["subject"], $response["content"]];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получаем мокнутые данные
	 *
	 * @return array
	 * @throws \cs_CurlError
	 * @throws cs_MockedDataIsNotFound
	 */
	protected static function _getMockedResponse(string $key):array {

		$mocked_response = fromJson(Type_Mock_Service::getRequestByKey($key));

		if ($mocked_response["status"] == "error") {
			throw new cs_MockedDataIsNotFound();
		}

		// подчищаем мокнутые данные
		Type_Mock_Service::resetRequest($key);

		return $mocked_response;
	}

	/**
	 * возвращает ключ, по которому мокается текст отправляемого сообщения
	 */
	protected static function _getKeyForMockedText(string $mail):string {

		return sprintf("mail_text_%s", Type_Hash_Mail::makeHash($mail));
	}
}