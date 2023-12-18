<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с сущностью публичного диалога
 */
class Type_Conversation_Public extends Type_Conversation_Default {

	/**
	 * создаем публичный диалог с владельцем внутри
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function create(int $user_id):array {

		// формируем users добавляя туда владельца
		$users = [];
		$users = Type_Conversation_Meta_Users::addMember($users, $user_id, Type_Conversation_Meta_Users::ROLE_OWNER);
		$extra = Type_Conversation_Meta_Extra::initExtra();

		// создаем новый conversation
		$meta_row = self::_createNewConversation(CONVERSATION_TYPE_PUBLIC_DEFAULT, ALLOW_STATUS_GREEN_LIGHT, $user_id, $users, $extra);

		// добавляем ключ диалога в таблице с личными диалогами пользователя
		Type_UserConversation_UserConversationRel::add($user_id, CONVERSATION_TYPE_PUBLIC_DEFAULT, $meta_row["conversation_map"]);

		return $meta_row;
	}
}