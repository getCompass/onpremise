<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Action для создания чата заметок у пользователя
 */
class Domain_Conversation_Action_NotesCreate {

	/**
	 * Создать чат заметок
	 *
	 * @param int    $user_id
	 * @param string $locale
	 *
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function do(int $user_id, string $locale):void {

		// проверяем, может нужный диалог уже имеется
		try {

			$user_conversation_rel_obj = Type_UserConversation_UserConversationRel::get($user_id, CONVERSATION_TYPE_SINGLE_NOTES);

			$set = [
				"is_hidden"   => 0,
				"is_favorite" => 1,
				"updated_at"  => time(),
				"version"     => Domain_User_Entity_Conversation_LeftMenu::generateVersion(0),
			];

			Gateway_Db_CompanyConversation_UserLeftMenu::set($user_id, $user_conversation_rel_obj->conversation_map, $set);

			// отправляем событие пользователю, что добавлен диалог в левом меню
			Gateway_Bus_Sender::conversationAdded($user_id, $user_conversation_rel_obj->conversation_map);
		} catch (\cs_RowIsEmpty) {

			$avatar_file_map = Domain_Group_Action_GetNotesDefaultAvatarFileMap::do();

			// создаем notes диалог
			$meta_row = Type_Conversation_Notes::create($user_id, $avatar_file_map, $locale);

			// сохраняем диалог за личным диалогом пользователя
			Type_UserConversation_UserConversationRel::add($user_id, CONVERSATION_TYPE_SINGLE_NOTES, $meta_row["conversation_map"]);
		}
	}
}
