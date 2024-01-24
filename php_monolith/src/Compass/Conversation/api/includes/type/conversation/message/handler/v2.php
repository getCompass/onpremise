<?php

namespace Compass\Conversation;

/**
 * класс для работы со структурой сообщений версии 2
 * все взаимодействие с сообщением нужной версии происходит через...
 * ... класс Type_Conversation_Message_Main::getHandler(), где возвращается класс-обработчик
 * для нужной версии сообщения
 *
 * таким образом достигается полная работоспособность со структурами сообщений разных версий
 * (но при этом важно чтобы в них совпадали функции)
 */
class Type_Conversation_Message_Handler_V2 extends Type_Conversation_Message_Handler_Default {

	// версия класса для работы с сообщением
	protected const _CURRENT_HANDLER_VERSION = 2;
}
