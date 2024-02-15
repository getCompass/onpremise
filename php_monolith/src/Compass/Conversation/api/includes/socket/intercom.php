<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Server\ServerProvider;
use BaseFrame\System\Locale;
use CompassApp\Pack\File;
use CompassApp\Pack\Message;
use CompassApp\Pack\Message\Conversation;

/**
 * Контроллер для сокет методов класса intercom
 */
class Socket_Intercom extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"addMessage",
		"deleteMessageList",
		"addMessageFromSupportBot",
		"getUserSupportConversationKey",
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

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Добавляем сообщение в чат службы поддержки
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function addMessage():array {

		$sender_user_id   = $this->post(\Formatter::TYPE_INT, "sender_user_id");
		$receiver_user_id = $this->post(\Formatter::TYPE_INT, "receiver_user_id");
		$type             = $this->post(\Formatter::TYPE_STRING, "type");
		$text             = $this->post(\Formatter::TYPE_STRING, "text", "");
		$file_key         = $this->post(\Formatter::TYPE_STRING, "file_key", "");

		if ((mb_strlen($text) < 1 && $type == "text") || (mb_strlen($file_key) < 1 && $type == "file")) {
			throw new ParamException("incorrect request");
		}

		try {

			$left_menu_row    = Type_Conversation_LeftMenu::getSupportGroupByUser($receiver_user_id);
			$conversation_map = $left_menu_row["conversation_map"];
		} catch (RowNotFoundException) {

			// если не нашли диалог со службой поддержки, то создаём его
			$avatar_file_map  = Domain_Group_Action_GetSupportDefaultAvatarFileMap::do();
			$conversation_map = Type_Conversation_Support::create($receiver_user_id, $avatar_file_map, Locale::LOCALE_RUSSIAN);
		}

		// получаем мету чата
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		try {
			Type_Conversation_Meta_Users::assertIsMember($sender_user_id, $meta_row["users"]);
		} catch (cs_UserIsNotMember) {

			// добавляем в группу
			Helper_Groups::doJoin($conversation_map, $sender_user_id, is_need_silent: true);
		}

		// формируем сообщение
		$message = match ($type) {
			"file" => Type_Conversation_Message_Main::getLastVersionHandler()::makeFile($sender_user_id, "", generateUUID(), File::doDecrypt($file_key)),
			"text" => self::_makeTextMessage($sender_user_id, $text, $meta_row),
			default => throw new ParamException("undefined message type={$type}"),
		};

		$message = Helper_Conversations::addMessage(
			$conversation_map, $message, $meta_row["users"], $meta_row["type"], $meta_row["conversation_name"], $meta_row["extra"]
		);

		return $this->ok([
			"message_key" => (string) Message::doEncrypt($message["message_map"]),
		]);
	}

	/**
	 * Формируем текстовое сообщение
	 *
	 * @param int    $sender_user_id
	 * @param string $text
	 * @param array  $meta_row
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	protected static function _makeTextMessage(int $sender_user_id, string $text, array $meta_row):array {

		// получаем меншены из текста
		$mention_user_id_list = Helper_Conversations::getMentionUserIdListFromText($meta_row, $text);

		// формируем сообщение
		$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeText($sender_user_id, $text, generateUUID());

		// добавляем меншены к сообщению если есть
		return Type_Conversation_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);
	}

	/**
	 * Удаляем сообщения
	 *
	 * @throws ParamException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function deleteMessageList():array {

		$sender_user_id   = $this->post(\Formatter::TYPE_INT, "sender_user_id");
		$receiver_user_id = $this->post(\Formatter::TYPE_INT, "receiver_user_id");
		$message_key_list = $this->post(\Formatter::TYPE_ARRAY, "message_key_list");

		$message_map_list = [];

		try {

			foreach ($message_key_list as $message_key) {
				$message_map_list[] = Conversation::doDecrypt($message_key);
			}
		} catch (\cs_DecryptHasFailed | \cs_UnpackHasFailed) {
			throw new ParamException("wrong message key");
		}

		$left_menu_row    = Type_Conversation_LeftMenu::getSupportGroupByUser($receiver_user_id);
		$conversation_map = $left_menu_row["conversation_map"];

		// получаем мету чата
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// удаляем сообщение
		try {
			Helper_Conversations::deleteMessageList($sender_user_id, $conversation_map, $meta_row["type"], $message_map_list, $meta_row, is_force_delete: true);
		} catch (cs_Message_UserHaveNotPermission) {
			return $this->error(10101, "User have not access to message");
		} catch (cs_Message_IsNotAllowForDelete) {
			return $this->error(10050, "You are NOT allowed to do this action");
		} catch (cs_Message_IsTimeNotAllowToDelete) {
			return $this->error(10051, "Timed out for delete message");
		} catch (cs_ConversationIsLocked) {
		}

		return $this->ok();
	}

	/**
	 * Оптравляем сообщение от лица бота поддержки
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function addMessageFromSupportBot():array {

		$text             = $this->post(\Formatter::TYPE_STRING, "text");
		$receiver_user_id = $this->post(\Formatter::TYPE_INT, "receiver_user_id");

		// получаем диалог
		$left_menu_row    = Type_Conversation_LeftMenu::getSupportGroupByUser($receiver_user_id);
		$conversation_map = $left_menu_row["conversation_map"];

		// создаем сообщение с текстом
		$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemBotText(
			SUPPORT_BOT_USER_ID,
			$text,
			generateUUID()
		);

		// отправляем приветственное сообщение в чат
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		Helper_Conversations::addMessage(
			$meta_row["conversation_map"], $message, $meta_row["users"], $meta_row["type"], $meta_row["conversation_name"], $meta_row["extra"]
		);

		return $this->ok();
	}

	/**
	 * Получаем чат со службой поддержки
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	public function getUserSupportConversationKey():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		if ($user_id < 1) {
			throw new ParamException("incorrect request");
		}

		// получаем диалог или создаем новый (мало ли)
		try {

			$left_menu_row    = Type_Conversation_LeftMenu::getSupportGroupByUser($user_id);
			$conversation_map = $left_menu_row["conversation_map"];
		} catch (RowNotFoundException) {

			// создаем диалог со службой поддержки
			$avatar_file_map  = Domain_Group_Action_GetSupportDefaultAvatarFileMap::do();
			$conversation_map = Type_Conversation_Support::create($user_id, $avatar_file_map, Locale::LOCALE_RUSSIAN);
		}

		return $this->ok([
			"support_conversation_key" => (string) \CompassApp\Pack\Conversation::doEncrypt($conversation_map),
		]);
	}
}