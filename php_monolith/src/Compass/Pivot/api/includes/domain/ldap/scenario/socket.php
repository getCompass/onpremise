<?php

namespace Compass\Pivot;

use BaseFrame\System\Locale;

/**
 * Сценарии для сокетов LDAP
 */
class Domain_Ldap_Scenario_Socket {

	/**
	 * Отправить код подтверждения на почту
	 *
	 * @param string $mail
	 * @param string $confirm_code
	 * @param string $template
	 *
	 * @return string
	 * @throws \queryException
	 */
	public static function sendMailConfirmCode(string $mail, string $confirm_code, string $template):string {

		// получаем конфиг с шаблонами для писем
		$config = getConfig("LOCALE_TEXT");

		[$title, $content] = Type_Mail_Content::make($config, $template, Locale::LOCALE_RUSSIAN, [
			"confirm_code" => addConfirmCodeDash($confirm_code),
		]);

		$message_id = generateUUID();

		// добавляем задачу на отправку
		Type_Mail_Queue::addTask($message_id, $mail, $title, $content, []);

		return $message_id;
	}
}