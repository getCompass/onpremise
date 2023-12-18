<?php

namespace Compass\Conversation;

use BaseFrame\Locale\Push\Message;

/**
 * Класс для локализации сообщений
 */
class Domain_Push_Entity_Locale_Message extends Message {

	public const CONVERSATION_ENTITY = "conversation"; // сообщение в чате

	protected bool $_is_group              = false;  // в группе ли сообщение
	protected bool $_is_support_bot_sender = false;  // сообщение от бота службы поддержки

	/**
	 * В группе ли написано сообщение
	 *
	 * @return $this
	 */
	public function setIsGroup():self {

		$this->_args_count++;
		$this->_is_group = true;

		return $this;
	}

	/**
	 * Отправитель сообщения – бот службы поддержки
	 *
	 * @return $this
	 */
	public function setIsSupportBotSender():self {

		$this->_is_support_bot_sender = true;

		return $this;
	}

	/**
	 * Получить результат локализации
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function getLocaleResult():array {

		// добавляем префикс group, если сообщение в группе
		if ($this->_is_group) {
			$this->_additional_key = "GROUP";
		}

		// перезаписываем префикс, если отправитель сообщения – бот службы поддержки
		if ($this->_is_support_bot_sender) {
			$this->_additional_key = "SUPPORT_BOT";
		}

		return parent::getLocaleResult();
	}
}
