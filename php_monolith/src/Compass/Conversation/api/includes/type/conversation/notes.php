<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с сущностью чата заметки
 */
class Type_Conversation_Notes extends Type_Conversation_Default {

	/**
	 * создаем чат заметки
	 *
	 * @param int    $user_id
	 * @param string $avatar_file_map
	 * @param string $locale
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function create(int $user_id, string $avatar_file_map, string $locale):array {

		// формируем users добавляя туда владельца
		$users = [];
		$users = Type_Conversation_Meta_Users::addMember($users, $user_id, Type_Conversation_Meta_Users::ROLE_OWNER);
		$extra = Type_Conversation_Meta_Extra::initExtra();

		// получаем имя чата заметки
		try {
			$group_name = Domain_Group_Entity_Company::getDefaultGroupNameByKey(Domain_Company_Entity_Config::NOTES_CONVERSATION_KEY_NAME, $locale);
		} catch (LocaleTextNotFound) {
			throw new ParseFatalException("cant find notes default name");
		}

		// создаем новый conversation
		$meta_row = self::_createNewConversation(CONVERSATION_TYPE_SINGLE_NOTES, ALLOW_STATUS_GREEN_LIGHT, $user_id, $users, $extra,
			$group_name, $avatar_file_map);

		// создаем запись в левом меню создателя
		$left_menu_row = self::_createUserCloudData(
			$user_id, $meta_row["conversation_map"], Type_Conversation_Meta_Users::ROLE_OWNER,
			CONVERSATION_TYPE_SINGLE_NOTES, Type_Conversation_Utils::ALLOW_STATUS_OK,
			count($users), $group_name, $avatar_file_map, 1, 0
		);

		// отправляем событие пользователю, что добавлен диалог в левом меню
		$prepared_left_menu_row  = Type_Conversation_Utils::prepareLeftMenuForFormat($left_menu_row);
		$formatted_left_menu_row = Apiv1_Format::leftMenu($prepared_left_menu_row);
		Gateway_Bus_Sender::conversationLeftMenuUpdated($user_id, $formatted_left_menu_row);

		// пушим событие, что пользователь присоединился к группе
		Gateway_Event_Dispatcher::dispatch(Type_Event_UserConversation_UserJoinedConversation::create(
			$user_id, $meta_row["conversation_map"], Type_Conversation_Meta_Users::ROLE_OWNER, time(), true
		));

		return $meta_row;
	}
}