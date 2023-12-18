<?php

namespace Compass\Conversation;

use CompassApp\Domain\User\Main;
use JetBrains\PhpStorm\ArrayShape;

/**
 * класс для работы с go_pusher
 */
class Gateway_Bus_Pusher {

	// -------------------------------------------------------
	// методы для работы с объектом push уведомления
	// -------------------------------------------------------

	/**
	 * формируем объект с пуш уведомлением для сообщения в диалоге
	 *
	 * @param string $conversation_map
	 * @param string $message_map
	 * @param string $push_title
	 * @param string $push_body
	 * @param array  $push_body_locale
	 * @param int    $event_type
	 * @param int    $event_additional_type
	 * @param int    $sender_user_id
	 * @param string $conversation_type
	 * @param int    $message_type
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @long
	 */
	#[ArrayShape(["badge_inc_count" => "int", "push_type" => "int", "event_type" => "int", "text_push" => "array", "is_need_force_push" => "int"])]
	public static function makeConversationMessagePushData(string $conversation_map, string $message_map,
										 string $push_title, string $push_body, array $push_body_locale,
										 int    $event_type, int $event_additional_type,
										 int    $sender_user_id, int $sender_user_type, string $conversation_type, int $message_type,
										 bool   $is_need_force_push = false,
										 array  $push_title_locale = []):array {

		$sender_user_type = $sender_user_type > 0 ? Main::USER_TYPE_SCHEMA[Main::getUserType($sender_user_type)] : "";

		// готовим ответ
		$output = [
			"badge_inc_count"    => 1,
			"push_type"          => 1,
			"event_type"         => $event_type | $event_additional_type,
			"text_push"          => [
				"company_id"        => COMPANY_ID,
				"title"             => $push_title,
				"body"              => $push_body,
				"body_localization" => $push_body_locale,
				"sender_user_id"    => $sender_user_id,
				"conversation_map"  => $conversation_map,
				"parent_key"        => \CompassApp\Pack\Conversation::doEncrypt($conversation_map),
				"collapse_id"       => sha1(\CompassApp\Pack\Message::doEncrypt($message_map)),
				"conversation_type" => Apiv1_Format::getConversationOutputType($conversation_type),
				"entity_type"       => "conversation_message",
				"entity_data"       => [
					"message_type"     => Apiv1_Format::getConversationMessageOutputType($message_type),
					"sender_user_type" => $sender_user_type,
				],
			],
			"is_need_force_push" => $is_need_force_push === true ? 1 : 0,
		];

		// если имеется локализация заголовка пуша, то добавляем к ответу
		if (count($push_title_locale) > 0) {
			$output["text_push"]["title_localization"] = $push_title_locale;
		}

		return $output;
	}
}
