<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use JetBrains\PhpStorm\ArrayShape;

/**
 * класс для форматирование сущностей под формат API
 * в коде мы оперируем своими структурами и понятиями
 * к этому классу обращаемся строго отдачей результата в API для форматирования стандартных сущностей
 */
class Apiv2_Format {

	// преобразование числового родительского типа треда в текстовый
	// при изменении обязательно добавь изменения в apiv1
	protected const _THREAD_PARENT_TYPE_SCHEMA = [
		PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE => "message",
		PARENT_ENTITY_TYPE_THREAD_MESSAGE       => "message",
		PARENT_ENTITY_TYPE_HIRING_REQUEST       => "hiring_request",
		PARENT_ENTITY_TYPE_DISMISSAL_REQUEST    => "dismissal_request",
	];

	/**
	 * приводит к формату Напоминание
	 */
	#[ArrayShape(["remind_id" => "int", "creator_user_id" => "int", "comment" => "string", "remind_at" => "int"])]
	public static function remind(int $remind_id, int $remind_at, int $creator_user_id, string $comment):array {

		return [
			"remind_id"       => (int) $remind_id,
			"creator_user_id" => (int) $creator_user_id,
			"comment"         => (string) $comment,
			"remind_at"       => (int) $remind_at,
		];
	}

	// мета треда
	// при изменении обязательно добавь изменения в apiv1
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
			"is_readonly"      => (int) $prepared_thread_meta["is_readonly"],
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
}

