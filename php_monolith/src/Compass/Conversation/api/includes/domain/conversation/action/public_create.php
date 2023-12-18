<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Action для создания публичного диалога у пользователя
 */
class Domain_Conversation_Action_PublicCreate {

	/**
	 *
	 * @param int $user_id
	 *
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function do(int $user_id):void {

		// проверяем, может нужнный диалог уже имеется
		try {
			Type_UserConversation_UserConversationRel::get($user_id, CONVERSATION_TYPE_PUBLIC_DEFAULT);
		} catch (\cs_RowIsEmpty) {

			// создаем public диалог
			Helper_Public::create($user_id);
		}
	}
}
