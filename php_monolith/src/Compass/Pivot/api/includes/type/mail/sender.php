<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * класс для отправки писем
 * @package Compass\Pivot
 */
class Type_Mail_Sender {

	/**
	 * Выбираем провайдера, через которого отправим письмо
	 *
	 * @return Type_Mail_Sender_AbstractProvider
	 */
	public static function getProvider():string|Type_Mail_Sender_AbstractProvider {

		if (ServerProvider::isTest()) {
			return Type_Mail_Sender_Mock::class;
		}

		return Type_Mail_Sender_PhpMailer::class;
	}

}