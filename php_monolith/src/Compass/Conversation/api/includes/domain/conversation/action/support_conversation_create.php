<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Server\ServerProvider;

/**
 * Action для создания чата с отделом поддержки у пользователя
 */
class Domain_Conversation_Action_SupportConversationCreate {

	/**
	 * Создать чат заметок
	 *
	 * @param int    $user_id
	 * @param string $locale
	 *
	 * @return string
	 * @throws LocaleTextNotFound
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	public static function do(int $user_id, string $locale):string {

		// если служба поддержки еще не выпущен на всех и не нужно создавать диалоги в этой компании
		if (!IS_PUBLIC_USER_SUPPORT && !in_array(COMPANY_ID, NEED_CREATE_SUPPORT_CONVERSATION_COMPANY_ID_LIST)) {
			return "";
		}

		if (ServerProvider::isOnPremise()) {
			return "";
		}

		// проверяем, может нужный диалог уже имеется
		try {

			$left_menu_row    = Type_Conversation_LeftMenu::getSupportGroupByUser($user_id);
			$conversation_map = $left_menu_row["conversation_map"];

			$set = [
				"is_hidden"  => 0,
				"updated_at" => time(),
				"version"    => Domain_User_Entity_Conversation_LeftMenu::generateVersion(0),
			];
			Gateway_Db_CompanyConversation_UserLeftMenu::set($user_id, $conversation_map, $set);

			// отправляем событие пользователю, что добавлен диалог в левом меню
			Gateway_Bus_Sender::conversationAdded($user_id, $conversation_map);
		} catch (RowNotFoundException) {

			$avatar_file_map = Domain_Group_Action_GetSupportDefaultAvatarFileMap::do();

			// создаем диалог с отделом поддержки
			$conversation_map = Type_Conversation_Support::create($user_id, $avatar_file_map, $locale);
		}

		// отправляем приветственное сообщение в чат
		Type_Conversation_Support::sendWelcomeMessage($conversation_map, $locale);

		return $conversation_map;
	}
}
