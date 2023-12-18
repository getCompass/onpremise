<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Server\ServerProvider;
use BaseFrame\System\Locale;

/**
 * Класс для работы с сущностью чата службы поддержки
 */
class Type_Conversation_Support extends Type_Conversation_Default {

	/**
	 * Создаем чат службы поддержки
	 *
	 * @param int    $user_id
	 * @param string $avatar_file_map
	 * @param string $locale
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public static function create(int $user_id, string $avatar_file_map, string $locale):string {

		if (ServerProvider::isOnPremise()) {
			throw new ParseFatalException("support conversation is disabled on on-premise environment");
		}

		// формируем users добавляя туда владельца
		$users = [];
		$users = Type_Conversation_Meta_Users::addMember($users, $user_id, Type_Conversation_Meta_Users::ROLE_OWNER);
		$extra = Type_Conversation_Meta_Extra::initExtra();

		// получаем имя чата заметки
		try {
			$group_name = Domain_Group_Entity_Company::getDefaultGroupNameByKey(Domain_Company_Entity_Config::SUPPORT_CONVERSATION_KEY_NAME, $locale);
		} catch (LocaleTextNotFound) {
			throw new ParseFatalException("cant find support default name");
		}

		// создаем новый conversation
		$conversation_type = CONVERSATION_TYPE_GROUP_SUPPORT;
		$meta_row          = self::_createNewConversation($conversation_type, ALLOW_STATUS_GREEN_LIGHT, $user_id, $users, $extra,
			$group_name, $avatar_file_map);

		// создаем запись в левом меню создателя
		self::_createUserCloudData(
			$user_id, $meta_row["conversation_map"], Type_Conversation_Meta_Users::ROLE_OWNER,
			$conversation_type, Type_Conversation_Utils::ALLOW_STATUS_OK,
			count($users), $group_name, $avatar_file_map, 0, 0
		);

		// пушим событие, что пользователь присоединился к группе
		Gateway_Event_Dispatcher::dispatch(Type_Event_UserConversation_UserJoinedConversation::create(
			$user_id, $meta_row["conversation_map"], Type_Conversation_Meta_Users::ROLE_OWNER, time(), true
		));

		// отправляем событие пользователю, что добавлен диалог в левом меню
		Gateway_Bus_Sender::conversationAdded($user_id, $meta_row["conversation_map"]);

		return $meta_row["conversation_map"];
	}

	/**
	 * Отправляем приветственное сообщение в чат
	 *
	 * @param string $conversation_map
	 * @param string $locale
	 *
	 * @return void
	 * @throws LocaleTextNotFound
	 * @throws ParamException
	 */
	public static function sendWelcomeMessage(string $conversation_map, string $locale):void {

		// получаем текст в зависимости от локализации
		$message_text = Locale::getText(getConfig("LOCALE_TEXT"), "support_bot", "welcome_message_text", locale: $locale);

		// создаем сообщение с текстом
		$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemBotText(
			SUPPORT_BOT_USER_ID,
			$message_text,
			generateUUID()
		);

		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// отправляем приветственное сообщение в чат
		Helper_Conversations::addMessage(
			$meta_row["conversation_map"], $message, $meta_row["users"], $meta_row["type"], $meta_row["conversation_name"], $meta_row["extra"]
		);
	}
}