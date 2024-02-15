<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * класс с вспомогательными функциями связанными с тредами
 */
class Type_Thread_Utils {

	public const CONVERSATION_ALLOW_STATUS_OK                  = 1;  // в диалог можно писать, все ок
	public const CONVERSATION_ALLOW_STATUS_MEMBER_IS_DISABLED  = 14; // в диалог нельзя писать, один из участников заблокирован в системе
	public const CONVERSATION_ALLOW_STATUS_MEMBER_IS_DELETED   = 15; // в диалог нельзя писать, один из участников удалил аккаунт в системе
	public const CONVERSATION_ALLOW_STATUS_USERBOT_IS_DISABLED = 20; // в диалог нельзя писать, пользовательский бот выключен
	public const CONVERSATION_ALLOW_STATUS_USERBOT_IS_DELETED  = 21; // в диалог нельзя писать, пользовательский бот удалён

	// допустимые значения file_source для отправки в сообщении
	protected const _ALLOW_FILE_SOURCE = [
		FILE_SOURCE_MESSAGE_DEFAULT,
		FILE_SOURCE_MESSAGE_IMAGE,
		FILE_SOURCE_MESSAGE_VIDEO,
		FILE_SOURCE_MESSAGE_AUDIO,
		FILE_SOURCE_MESSAGE_DOCUMENT,
		FILE_SOURCE_MESSAGE_ARCHIVE,
		FILE_SOURCE_MESSAGE_VOICE,
	];

	/**
	 * подготавливает threadMeta для передачи в Apiv1_Format
	 *
	 * @throws \parseException
	 * @long - switch..case
	 */
	public static function prepareThreadMetaForFormat(array $meta_row, int $user_id, bool $is_force_new_thread_meta = false):array {

		$output = self::_makeDefaultThreadMetaOutput($meta_row, $user_id, $is_force_new_thread_meta);

		// в зависимости от parent_type формируем объект parent
		$parent_type = Type_Thread_ParentRel::getType($meta_row["parent_rel"]);
		switch ($parent_type) {

			case PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE:
			case PARENT_ENTITY_TYPE_THREAD_MESSAGE:

				$parent_map       = Type_Thread_ParentRel::getMap($meta_row["parent_rel"]);
				$creator_user_id  = Type_Thread_ParentRel::getCreatorUserId($meta_row["parent_rel"]);
				$output["parent"] = [
					"type"            => $parent_type,
					"map"             => $parent_map,
					"message_map"     => $parent_map,
					"creator_user_id" => $creator_user_id,
				];
				break;

			case PARENT_ENTITY_TYPE_HIRING_REQUEST:
			case PARENT_ENTITY_TYPE_DISMISSAL_REQUEST:

				$parent_id        = Type_Thread_ParentRel::getMap($meta_row["parent_rel"]);
				$creator_user_id  = Type_Thread_ParentRel::getCreatorUserId($meta_row["parent_rel"]);
				$output["parent"] = [
					"type"            => $parent_type,
					"request_id"      => $parent_id,
					"creator_user_id" => $creator_user_id,
				];
				break;

			default:
				throw new ParseFatalException("incorrect parent type");
		}

		return $output;
	}

	// получаем стандартную структуру thread_meta
	protected static function _makeDefaultThreadMetaOutput(array $meta_row, int $user_id, bool $is_force_new_thread_meta):array {

		$output = [
			"thread_map"  => $meta_row["thread_map"],
			"is_readonly" => $meta_row["is_readonly"],
			"created_at"  => $meta_row["created_at"],
			"updated_at"  => $meta_row["updated_at"],
		];

		// если передан заголовок - прикрепляем last_sender_data и всегда возвращаем полное количество сообщений
		if (Type_System_Legacy::isNewThreadMeta() || $is_force_new_thread_meta) {

			$message_count              = $meta_row["message_count"];
			$output["last_sender_data"] = Type_Thread_Meta_LastSenderData::prepareForFormat($meta_row["last_sender_data"]);
		} else {
			$message_count = self::_getLegacyMessageCount($meta_row, $user_id);
		}
		$output["message_count"] = $message_count;

		return self::_getSenderUserListOutput($output, $meta_row);
	}

	protected static function _getLegacyMessageCount(array $meta_row, int $user_id):int {

		// если пользователя нет в мете - просто возвращаем как есть
		if (!isset($meta_row["users"][$user_id])) {
			return $meta_row["message_count"];
		}

		// иначе отнимаем скрытые от общего количества
		return $meta_row["message_count"] - Type_Thread_Meta_Users::getCountHiddenMessage($meta_row["users"][$user_id]);
	}

	// получаем список отправителей сообщений в треде
	protected static function _getSenderUserListOutput(array $output, array $meta_row):array {

		// получаем список отправителей сообщений в треде
		$sender_user_list = self::getSenderUserList($meta_row["sender_order"], true);

		// добавляем отправителей
		$output["last_sender_user_list"] = array_slice($sender_user_list, -5);
		$output["sender_user_list"]      = $sender_user_list;

		return $output;
	}

	// получаем список отправителей сообщений в треде
	public static function getSenderUserList(array $sender_order, bool $is_new_sender_list):array {

		$output = [];

		// проходимся по sender_order задом наперед
		$sender_order = array_reverse($sender_order);
		$in           = [];
		foreach ($sender_order as $v) {

			// если уже имеется такой идентификатор
			if (isset($in[$v])) {
				continue;
			}

			$in[$v]   = $v;
			$output[] = $v;

			// если старые отправители и набралось их достаточно
			if (!$is_new_sender_list && count($output) == 5) {
				break;
			}
		}

		return array_reverse($output);
	}

	// подготавливает getItem для передачи в Apiv1_Format
	public static function prepareGetItemForFormat(array $thread_menu_row):array {

		return [
			"thread_map"            => $thread_menu_row["thread_map"],
			"is_follow"             => $thread_menu_row["is_follow"],
			"is_favorite"           => $thread_menu_row["is_favorite"],
			"unread_count"          => $thread_menu_row["unread_count"],
			"created_at"            => $thread_menu_row["created_at"],
			"updated_at"            => $thread_menu_row["updated_at"],
			"last_read_message_map" => $thread_menu_row["last_read_message_map"],
			"parent_type"           => Type_Thread_ParentRel::getType($thread_menu_row["parent_rel"]),
		];
	}

	// подготавливаем menu для передачи в Apiv1_Format
	public static function prepareThreadMenuForFormat(array $thread_menu_row):array {

		$output = [
			"thread_map"   => $thread_menu_row["thread_map"],
			"is_follow"    => $thread_menu_row["is_follow"],
			"is_muted"     => $thread_menu_row["is_muted"],
			"is_favorite"  => $thread_menu_row["is_favorite"],
			"unread_count" => $thread_menu_row["unread_count"],
			"created_at"   => $thread_menu_row["created_at"],
			"updated_at"   => $thread_menu_row["updated_at"],
			"parent_type"  => Type_Thread_ParentRel::getType($thread_menu_row["parent_rel"]),
		];

		if (isset($thread_menu_row["last_read_message_map"]) && mb_strlen($thread_menu_row["last_read_message_map"]) > 0) {
			$output["last_read_message_map"] = $thread_menu_row["last_read_message_map"];
		}

		return $output;
	}

	// получаем подпись
	public static function getSignatureWithCustomSalt(array $thread_list, int $time, string $salt):string {

		$thread_list[] = $time;
		sort($thread_list);

		$json = toJson($thread_list);

		// зашифровываем данные
		$iv_length   = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv          = substr(ENCRYPT_IV_DEFAULT, 0, $iv_length);
		$binary_data = openssl_encrypt($json, ENCRYPT_CIPHER_METHOD, $salt, 0, $iv);

		return md5($binary_data) . "_" . $time;
	}

	// проверяем подпись
	public static function verifySignatureWithCustomSalt(array $thread_list, string $signature, string $salt):bool {

		$temp = explode("_", $signature);

		// проверяем, корректная ли пришла подпись
		if (count($temp) != 2) {
			return false;
		}

		// сверяем подпись
		$time = intval($temp[1]);
		if ($signature != self::getSignatureWithCustomSalt($thread_list, $time, $salt)) {
			return false;
		}

		return true;
	}

	// строим список данных сообщений и располагаем в нем элементы по порядку
	public static function parseRawMessageList(array $client_messages_raw, bool $is_quote = false):array {

		self::_throwOnInvalidMessageChunkFormat($client_messages_raw);

		// считаем количество сообщений и каким сообщениям нужен trim
		$count_message     = count($client_messages_raw);
		$key_message_ltrim = 0;
		$key_message_rtrim = $count_message - 1;

		$output = [];
		foreach ($client_messages_raw as $k => $v) {

			// сообщение должно быть массивом а необходимые поля должны быть объявлены
			self::_throwIfInvalidMessageChunkHasCorrectFields($v);
			$order = intval($v["order"]);

			// номер должен быть уникален для каждого сообщения - иначе выбрасываем исключение
			if (isset($output[$order])) {
				self::_throwOnInvalidMessageOrder();
			}

			// собираем output
			$output = self::_makeOutputForRawMessageList($k, $key_message_ltrim, $key_message_rtrim, $order, $v, $output);

			if (isset($v["file_key"]) && $v["file_key"] !== false) {
				$output[$order]["file_map"] = self::_tryGetFileMapFromKey($v["file_key"]);
			}
			if (mb_strlen($output[$order]["text"]) < 1 && $output[$order]["file_map"] === false && $is_quote === false) {
				throw new ParamException(__CLASS__ . ": empty text and file_map");
			}
		}

		// сортируем массив по ключам для восстановления порядка сообщений по order
		ksort($output);
		return array_values($output);
	}

	// проверяет, что количесвто кусков сообщения и их формат
	protected static function _throwOnInvalidMessageChunkFormat(array $client_messages_raw):void {

		// количество кусков не должно превышать половину емкости блока
		if (count($client_messages_raw) > Type_Thread_Message_Block::MESSAGE_PER_BLOCK_LIMIT / 2) {
			throw new cs_Message_IsTooLong(__CLASS__ . ": too many message blocks");
		}

		// ожидаем как минимум одно сообщение
		if (count($client_messages_raw) == 0) {
			throw new ParamException(__CLASS__ . ": empty client message list");
		}
	}

	/**
	 * проверяет наличие в куске сообщения необходимых полей и их типы
	 *
	 * @param $message_chunk
	 *
	 * @throws \paramException
	 * @mixed внутри все проверки :)
	 */
	protected static function _throwIfInvalidMessageChunkHasCorrectFields($message_chunk):void {

		// если все типы параметров корректны
		if (Type_Api_Validator::isCorrectTypeClientMessageItem($message_chunk)) {
			return;
		}

		throw new ParamException(__CLASS__ . ": invalid client message list format");
	}

	// выбрасывавет исключение о неправильном порядке сообщений и пишет статистику
	protected static function _throwOnInvalidMessageOrder():void {

		throw new ParamException(__CLASS__ . ": invalid message order");
	}

	// собираем output для raw_message_list
	protected static function _makeOutputForRawMessageList(string $key, int $key_message_ltrim, int $key_message_rtrim, int $order, array $message_chunk, array $output):array {

		$is_ltrim = false;
		if ($key == $key_message_ltrim) {
			$is_ltrim = true;
		}

		$is_rtrim = false;
		if ($key == $key_message_rtrim) {
			$is_rtrim = true;
		}

		$output[$order] = [
			"client_message_id" => self::_prepareRawClientMessageId($message_chunk["client_message_id"]),
			"text"              => self::_prepareRawMessageText($message_chunk["text"], $is_ltrim, $is_rtrim),
			"file_map"          => false,
			"file_name"         => isset($message_chunk["file_name"]) ? Type_Api_Filter::sanitizeFileName($message_chunk["file_name"]) : "",
		];
		return $output;
	}

	// подготавливаем client_message_id для сообщения
	protected static function _prepareRawClientMessageId(string $client_message_id):string {

		// валидируем client_message_id
		$client_message_id = Type_Api_Filter::sanitizeClientMessageId($client_message_id);
		if (mb_strlen($client_message_id) <= 0) {
			throw new ParamException(__CLASS__ . ": incorrect client_message_id");
		}

		return $client_message_id;
	}

	// подготавливаем $message_text для сообщения
	protected static function _prepareRawMessageText(string $message_text, bool $is_ltrim, bool $is_rtrim):string {

		// валидируем и преобразуем текст сообщения
		$text = Type_Api_Filter::replaceEmojiWithShortName($message_text);
		if (mb_strlen($text) > Type_Api_Filter::MAX_MESSAGE_TEXT_LENGTH) {
			throw new cs_Message_IsTooLong(__CLASS__ . ": message is too long");
		}

		return Type_Api_Filter::sanitizeMessageText($text, $is_ltrim, $is_rtrim);
	}

	// пробуем получить file_map из ключа
	protected static function _tryGetFileMapFromKey(string $file_key):string {

		// преобразуем key в map
		$file_map = \CompassApp\Pack\File::tryDecrypt($file_key);

		// если file_source не входит в список разрешенных для отправки в сообщении
		$file_source = \CompassApp\Pack\File::getFileSource($file_map);
		if (!in_array($file_source, self::_ALLOW_FILE_SOURCE)) {
			throw new ParamException("Incorrect file source");
		}

		return $file_map;
	}

	/**
	 * тип родительской сущности принадлежит сообщению диалога
	 *
	 */
	public static function isConversationMessageParent(int $parent_entity_type):bool {

		return $parent_entity_type == PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE;
	}

	/**
	 * тип родительской сущности принадлежит сообщению треда
	 *
	 */
	public static function isThreadMessageParent(int $parent_entity_type):bool {

		return $parent_entity_type == PARENT_ENTITY_TYPE_THREAD_MESSAGE;
	}

	/**
	 * тип родительской сущности принадлежит заявке найма
	 *
	 */
	public static function isHiringRequestParent(int $parent_entity_type):bool {

		return $parent_entity_type == PARENT_ENTITY_TYPE_HIRING_REQUEST;
	}

	/**
	 * тип родительской сущности принадлежит заявке увольнения
	 *
	 */
	public static function isDismissalRequestParent(int $parent_entity_type):bool {

		return $parent_entity_type == PARENT_ENTITY_TYPE_DISMISSAL_REQUEST;
	}

	/**
	 * тип родительской сущности принадлежит заявке найма/увольнения
	 *
	 */
	public static function isHiringOrDismissalRequestParent(int $parent_entity_type):bool {

		return self::isHiringRequestParent($parent_entity_type) || self::isDismissalRequestParent($parent_entity_type);
	}

	/**
	 * проверяем что тред избранный
	 */
	public static function isFavorite(array $thread_menu_row):bool {

		return $thread_menu_row["is_favorite"] == 1;
	}
}