<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для форматирование сущностей под формат API
 * в коде мы оперируем своими структурами и понятиями
 * к этому классу обращаемся строго отдачей результата в API для форматирования стандартных сущностей
 */
class Apiv1_Format {

	// массив для преобразования внутреннего типа во внешний
	protected const _USER_TYPE_SCHEMA = [
		Type_User_Main::USER_HUMAN       => "user",
		Type_User_Main::USER_SYSTEM_BOT  => "system_bot",
		Type_User_Main::USER_SUPPORT_BOT => "support_bot",
		Type_User_Main::USER_OUTER_BOT   => "bot",
	];

	// преобразование числового типа сообщения треда в текстовый
	protected const _THREAD_MESSAGE_TYPE_SCHEMA = [
		THREAD_MESSAGE_TYPE_TEXT                    => "text",
		THREAD_MESSAGE_TYPE_CONVERSATION_TEXT       => "text",
		THREAD_MESSAGE_TYPE_FILE                    => "file",
		THREAD_MESSAGE_TYPE_CONVERSATION_FILE       => "file",
		THREAD_MESSAGE_TYPE_QUOTE                   => "quote",
		THREAD_MESSAGE_TYPE_MASS_QUOTE              => "mass_quote",
		THREAD_MESSAGE_TYPE_CONVERSATION_MASS_QUOTE => "mass_quote",
		THREAD_MESSAGE_TYPE_DELETED                 => "deleted",
		THREAD_MESSAGE_TYPE_SYSTEM                  => "system",
		THREAD_MESSAGE_TYPE_CONVERSATION_REPOST     => "repost",
		THREAD_MESSAGE_TYPE_CONVERSATION_CALL       => "call",
		THREAD_MESSAGE_TYPE_REPOST                  => "repost",
		THREAD_MESSAGE_TYPE_SYSTEM_BOT_REMIND       => "system_bot_remind",
	];

	// преобразование числового родительского типа треда в текстовый
	protected const _THREAD_PARENT_TYPE_SCHEMA = [
		PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE => "message",
		PARENT_ENTITY_TYPE_THREAD_MESSAGE       => "message",
		PARENT_ENTITY_TYPE_HIRING_REQUEST       => "hiring_request",
		PARENT_ENTITY_TYPE_DISMISSAL_REQUEST    => "dismissal_request",
	];

	// массив для преобразования числового type файла в строковый
	protected const _FILE_TYPE_SCHEMA = [
		FILE_TYPE_DEFAULT  => "file",
		FILE_TYPE_IMAGE    => "image",
		FILE_TYPE_VIDEO    => "video",
		FILE_TYPE_AUDIO    => "audio",
		FILE_TYPE_DOCUMENT => "document",
		FILE_TYPE_ARCHIVE  => "archive",
		FILE_TYPE_VOICE    => "voice",
	];

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	// элемент тред меню
	public static function threadMenu(array $thread_menu_info):array {

		$output = [
			"thread_map"   => (string) $thread_menu_info["thread_map"],
			"is_follow"    => (int) $thread_menu_info["is_follow"],
			"is_muted"     => (int) $thread_menu_info["is_muted"],
			"is_favorite"  => (int) $thread_menu_info["is_favorite"],
			"unread_count" => (int) ($thread_menu_info["unread_count"] < 0 ? 0 : $thread_menu_info["unread_count"]),
			"created_at"   => (int) $thread_menu_info["created_at"],
			"updated_at"   => (int) $thread_menu_info["updated_at"],
			"parent_type"  => (string) self::_THREAD_PARENT_TYPE_SCHEMA[$thread_menu_info["parent_type"]],
		];

		if (isset($thread_menu_info["last_read_message_map"])) {

			$output["last_read"] = (object) [
				"message_map" => (string) $thread_menu_info["last_read_message_map"],
			];
		}

		return $output;
	}

	// тип родительской сущности
	public static function parentType(int $parent_type):string {

		return (string) self::_THREAD_PARENT_TYPE_SCHEMA[$parent_type];
	}

	// мета треда
	public static function threadMeta(array $prepared_thread_meta):array {

		$output = self::_makeThreadMeta($prepared_thread_meta);

		// в зависимости от parent_type формируем объект parent
		switch ($prepared_thread_meta["parent"]["type"]) {

			case PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE:
			case PARENT_ENTITY_TYPE_THREAD_MESSAGE:

				$output["parent"]["message_map"]     = (string) $prepared_thread_meta["parent"]["map"];
				$output["parent"]["creator_user_id"] = (int) $prepared_thread_meta["parent"]["creator_user_id"];
				break;

			case PARENT_ENTITY_TYPE_HIRING_REQUEST:
			case PARENT_ENTITY_TYPE_DISMISSAL_REQUEST:

				$output["parent"]["request_id"]      = (int) $prepared_thread_meta["parent"]["request_id"];
				$output["parent"]["creator_user_id"] = (int) $prepared_thread_meta["parent"]["creator_user_id"];
				break;

			default:
				throw new ParseFatalException("incorrect parent type");
		}

		$output["parent"] = (object) $output["parent"];

		return $output;
	}

	// формируем стандратную структуру для threadMeta
	protected static function _makeThreadMeta(array $prepared_thread_meta):array {

		$output = [
			"thread_map"       => (string) $prepared_thread_meta["thread_map"],
			"created_at"       => (int) $prepared_thread_meta["created_at"],
			"updated_at"       => (int) $prepared_thread_meta["updated_at"],
			"message_count"    => (int) $prepared_thread_meta["message_count"],
			"parent"           => [
				"type" => (string) self::_THREAD_PARENT_TYPE_SCHEMA[$prepared_thread_meta["parent"]["type"]],
			],
			"sender_user_list" => (array) $prepared_thread_meta["sender_user_list"],
		];

		// прикрепляем к ответу если есть
		if (isset($prepared_thread_meta["last_sender_data"])) {
			$output["last_sender_data"] = (array) self::_formatLastSenderData($prepared_thread_meta["last_sender_data"]);
		}

		if (isset($prepared_thread_meta["last_sender_user_list"])) {
			$output["last_sender_user_list"] = (array) self::_formatLastSenderUserList($prepared_thread_meta["last_sender_user_list"]);
		}

		return $output;
	}

	// форматируем last_sender_data для ответа
	protected static function _formatLastSenderData(array $last_sender_data):array {

		$output = [];
		foreach ($last_sender_data as $item) {

			$output[] = [
				"thread_message_index" => (int) $item["thread_message_index"],
				"user_id"              => (int) $item["user_id"],
			];
		}
		return $output;
	}

	// форматируем last_sender_user_list для ответа
	protected static function _formatLastSenderUserList(array $last_sender_user_list):array {

		$output = [];
		foreach ($last_sender_user_list as $item) {
			$output[] = (int) $item;
		}
		return $output;
	}

	// сообщение из тредов
	public static function threadMessage(array $prepared_message):array {

		$output = self::_makeThreadMessage($prepared_message);

		// добавляем к сообщению тред если имется
		$output = self::_addThreadIfExist($prepared_message, $output);

		// добавляем к сообщению лист ссылок если имеются
		$output = self::_addLinkListIfExist($prepared_message, $output);

		// добавляем к сообщению превью если имеются
		$output = self::_addPreviewIfExist($prepared_message, $output);

		// получаем data в зависимости от типа файла
		$data = self::_makeDataForThreadMessage($output, $prepared_message);

		// добавляем data к сообщению
		$output["data"] = (object) $data;

		return $output;
	}

	/**
	 * Функция для формирования стандартной структуры threadMessage
	 *
	 * @param array $prepared_message
	 *
	 * @return array
	 */
	protected static function _makeThreadMessage(array $prepared_message):array {

		$output = [
			"message_map"          => (string) $prepared_message["message_map"],
			"block_id"             => (int) $prepared_message["block_id"],
			"is_edited"            => (int) $prepared_message["is_edited"],
			"is_archived"          => (int) 0,
			"message_index"        => (int) $prepared_message["message_index"],
			"sender_id"            => (int) $prepared_message["sender_id"],
			"created_at"           => (int) $prepared_message["created_at"],
			"allow_edit_till"      => (int) $prepared_message["allow_edit_till"],
			"allow_delete_till"    => (int) $prepared_message["allow_delete_till"],
			"type"                 => (string) self::_THREAD_MESSAGE_TYPE_SCHEMA[$prepared_message["type"]],
			"client_message_id"    => (string) $prepared_message["client_message_id"],
			"text"                 => (string) $prepared_message["text"],
			"reaction_list"        => (array) $prepared_message["reaction_list"],
			"mention_user_id_list" => (array) arrayValuesInt($prepared_message["mention_user_id_list"]),
			"platform"             => (string) $prepared_message["platform"],
			"last_message_edited"  => (int) ($prepared_message["last_message_text_edited_at"] ?? 0),
			"last_reaction_edited" => (int) ($prepared_message["last_reaction_edited"] ?? 0),
			"remind"               => (object) ($prepared_message["remind"] ?? []),
		];

		// добавляем мапу сущности, к которой принадлежит сообщение
		return array_merge($output, self::_attachParentMap($prepared_message));
	}

	/**
	 * Добавить мапу сущности к сообщения в зависимости от типа
	 *
	 * @param array $prepared_message
	 *
	 * @return array
	 */
	protected static function _attachParentMap(array $prepared_message):array {

		$output = [];

		if (isset($prepared_message["thread_map"])) {
			$output["thread_map"] = (string) $prepared_message["thread_map"];
		}

		if (isset($prepared_message["conversation_map"])) {
			$output["conversation_map"] = (string) $prepared_message["conversation_map"];
		}

		return $output;
	}

	/**
	 * Возвращает тип сообщения для фронта на основе его свойства type
	 *
	 * @throws \parseException
	 */
	public static function getThreadMessageOutputType(int $message_type):string {

		if (!isset(self::_THREAD_MESSAGE_TYPE_SCHEMA[$message_type])) {

			throw new ParseFatalException("there is no format output for message type {$message_type}");
		}

		return self::_THREAD_MESSAGE_TYPE_SCHEMA[$message_type];
	}

	// добавляем к выходному массиву тред если имеется
	protected static function _addThreadIfExist(array $prepared_message, array $output):array {

		// если к сообщению существует тред
		if (isset($prepared_message["child_thread"]["thread_map"])) {

			$output["child_thread"] = (object) [
				"thread_map" => (string) $prepared_message["child_thread"]["thread_map"],
			];
		}

		return $output;
	}

	// добавляем к выходному массиву список ссылок если имеется
	protected static function _addLinkListIfExist(array $prepared_message, array $output):array {

		// если в сообщении есть список ссылок
		if (isset($prepared_message["link_list"])) {

			foreach ($prepared_message["link_list"] as $key => $link) {

				if (isset($link["original_link"])) {
					$prepared_message["link_list"][$key]["redirect_link"] = $link["original_link"];
				}
			}

			$output["link_list"] = (array) $prepared_message["link_list"];
		}

		return $output;
	}

	// добавляем к выходному массиву превью если имеются
	protected static function _addPreviewIfExist(array $prepared_message, array $output):array {

		// если прикреплено "простое" превью - не прикрепляем к сообщению
		if (isset($prepared_message["preview_type"])) {

			if ($prepared_message["preview_type"] === PREVIEW_TYPE_SIMPLE) {
				return $output;
			}
			$output["preview_type"] = (string) Type_Preview_Main::PREVIEW_TYPE_SCHEMA[$prepared_message["preview_type"]];
		}

		// если в сообщении есть превью
		if (isset($prepared_message["preview_map"])) {
			$output["preview_map"] = (string) $prepared_message["preview_map"];
		}

		// если в сообщении есть preview_image
		if (isset($prepared_message["preview_image"])) {
			$output["preview_image"] = (array) $prepared_message["preview_image"];
		}

		return $output;
	}

	// формируем data в зависимости от типа файла
	// @long
	protected static function _makeDataForThreadMessage(array $output, array $prepared_message):array {

		// формируем data по типу сообщения
		switch ($output["type"]) {

			case "text":
			case "deleted":

				$data = [];
				break;

			case "file":

				$data = [
					"file_map"  => (string) $prepared_message["data"]["file_map"],
					"file_type" => (string) self::_FILE_TYPE_SCHEMA[$prepared_message["data"]["file_type"]],
				];

				if (isset($prepared_message["data"]["file_name"])) {
					$data["file_name"] = (string) $prepared_message["data"]["file_name"];
				}

				if (isset($prepared_message["data"]["file_uid"])) {
					$data["file_uid"] = (string) $prepared_message["data"]["file_uid"];
				}

				if (isset($prepared_message["data"]["file_width"]) && isset($prepared_message["data"]["file_height"])) {

					$data["file_width"]  = (int) $prepared_message["data"]["file_width"];
					$data["file_height"] = (int) $prepared_message["data"]["file_height"];
				}

				break;

			case "quote":

				$data = [
					"quoted_message" => (object) self::threadMessage($prepared_message["data"]["quoted_message"]),
				];

				break;

			case "mass_quote":
			case "system_bot_remind":

				$data = [
					"quoted_message_list" => (array) [],
				];

				foreach ($prepared_message["data"]["quoted_message_list"] as $v) {
					$data["quoted_message_list"][] = (object) self::threadMessage($v);
				}
				$data["quoted_message_count"] = (int) $prepared_message["data"]["quoted_message_count"];

				break;

			case "repost":

				$data = [
					"reposted_message_list" => (array) [],
				];

				foreach ($prepared_message["data"]["reposted_message_list"] as $v) {
					$data["reposted_message_list"][] = (object) self::threadMessage($v);
				}
				break;

			case "call":

				$data = [
					"call_map" => (string) $prepared_message["data"]["call_map"],
				];

				if (isset($prepared_message["data"]["call_report_id"], $prepared_message["data"]["call_duration"])) {

					$data["call_report_id"] = (int) $prepared_message["data"]["call_report_id"];
					$data["call_duration"]  = (int) $prepared_message["data"]["call_duration"];
				}
				break;

			case "system":

				$data = [
					"system_message_type" => (string) $prepared_message["data"]["system_message_type"],
					"user_id"             => (int) $prepared_message["data"]["user_id"],
				];
				break;

			default:
				throw new ParseFatalException("message type is not available");
		}

		return $data;
	}

	// один элемент списка тредов
	public static function getMenuItem(array $prepared_thread_meta, array $parent_entity, bool $is_need_parent_info = false):array {

		$output = [
			"thread_meta" => (object) Apiv1_Format::threadMeta($prepared_thread_meta),
			"parent_type" => (string) Apiv1_Format::parentType($parent_entity["parent_type"]),
			"parent_data" => [],
		];

		switch ($parent_entity["parent_type"]) {

			case PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE:
			case PARENT_ENTITY_TYPE_THREAD_MESSAGE:
				$output["parent_data"] = (object) ["message" => (object) $parent_entity["parent_entity"]];
				break;

			case PARENT_ENTITY_TYPE_HIRING_REQUEST:
			case PARENT_ENTITY_TYPE_DISMISSAL_REQUEST:
				$output["parent_data"] = (object) ["request" => (object) $parent_entity["parent_entity"]];
				break;
		}

		if ($is_need_parent_info) {
			$output["parent_info"] = (object) $parent_entity["parent_entity"];
		}

		if (isset($parent_entity["reaction_user_list"])) {
			$output["parent_data"]->reaction_user_list = (array) $parent_entity["reaction_user_list"];
		}

		if (isset($parent_entity["remind"])) {
			$output["parent_data"]->remind = (object) $parent_entity["remind"];
		}

		return $output;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

}

