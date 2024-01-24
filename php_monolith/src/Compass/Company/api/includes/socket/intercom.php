<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * Класс для работы с чатом поддержки
 */
class Socket_Intercom extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"addMessageFromSupportBot",
	];

	/**
	 * Переопределяем родительский work
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 */
	public function work(string $method_name, int $method_version, array $post_data, int $user_id, array $extra):array {

		// действия с intercom не доступны на on-premise окружении
		if (ServerProvider::isOnPremise()) {
			throw new ParseFatalException("action is not allowed on this environment");
		}

		return parent::work($method_name, $method_version, $post_data, $user_id, $extra);
	}

	/**
	 * Отправляем сообщение в чат поддержки от имени бота
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function addMessageFromSupportBot():array {

		$receiver_user_id = $this->post(\Formatter::TYPE_INT, "receiver_user_id");
		$text             = $this->post(\Formatter::TYPE_STRING, "text");

		// отправляем сообщение
		Gateway_Socket_Conversation::addMessageFromSupportBot($receiver_user_id, $text);

		return $this->ok();
	}
}
