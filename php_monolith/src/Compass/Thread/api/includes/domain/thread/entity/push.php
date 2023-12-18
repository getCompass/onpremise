<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use CompassApp\Domain\User\Main;
use JetBrains\PhpStorm\ArrayShape;
use CompassApp\Domain\Member\Struct\Main as MemberStruct;

/**
 * класс для работы с обьектами пушей
 */
class Domain_Thread_Entity_Push {

	/**
	 * Создаем пуш-уведомление
	 *
	 * @param array  $message
	 * @param array  $meta_row
	 * @param string $location_type
	 * @param array  $additional_data
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function makePushData(array $message, array $meta_row, string $location_type, array $additional_data = []):array {

		$parent_type = Type_Thread_ParentRel::getType($meta_row["parent_rel"]);
		$parent_id   = Type_Thread_ParentRel::getMap($meta_row["parent_rel"]);

		$sender_id = Type_Thread_Message_Main::getHandler($message)::getSenderUserId($message);

		$sender_member_info = null;

		try {

			if ($sender_id > 0) {
				$sender_member_info = Gateway_Bus_CompanyCache::getMember($sender_id);
			}
		} catch (\cs_RowIsEmpty) {
			// не нашли и ладно, надо разрулить дальше
		}

		return self::_makeThreadMessagePushData(
			$message,
			$meta_row["thread_map"],
			Type_Thread_Message_Main::getHandler($message)::getMessageMap($message),
			Type_Thread_Message_Main::getHandler($message)::getPushBodyLocale($message),
			Type_Thread_Message_Main::getHandler($message)::getEventType($message, $location_type),
			Type_Thread_Message_Main::getHandler($message)::getSenderUserId($message),
			$sender_member_info,
			$parent_type,
			$parent_id,
			Type_Thread_Message_Main::getHandler($message)::getType($message),
			$additional_data
		);
	}

	/**
	 * Формируем объект с пуш уведомлением для сообщения в треде
	 *
	 * @long
	 *
	 * @param array             $message
	 * @param string            $thread_map
	 * @param string            $message_map
	 * @param array             $push_body_locale
	 * @param int               $event_type
	 * @param int               $sender_user_id
	 * @param MemberStruct|null $sender_member_info
	 * @param int               $parent_type
	 * @param mixed             $parent_id
	 * @param int               $message_type
	 * @param array             $additional_data
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	#[ArrayShape(["badge_inc_count" => "int", "push_type" => "int", "event_type" => "int", "text_push" => "array", "is_need_force_push" => "int"])]
	protected static function _makeThreadMessagePushData(array $message, string $thread_map, string $message_map, array $push_body_locale,
									     int   $event_type, int $sender_user_id, MemberStruct|null $sender_member_info, int $parent_type,
									     mixed $parent_id, int $message_type, array $additional_data):array {

		$parent_id = match ($parent_type) {

			PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE                                 => \CompassApp\Pack\Message\Conversation::doEncrypt($parent_id),
			PARENT_ENTITY_TYPE_HIRING_REQUEST, PARENT_ENTITY_TYPE_DISMISSAL_REQUEST => $parent_id,
		};

		// имя родителя треда
		$parent_name_type = match ($parent_type) {

			PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE => "conversation_message",
			PARENT_ENTITY_TYPE_DISMISSAL_REQUEST    => "dismissal_request",
			PARENT_ENTITY_TYPE_HIRING_REQUEST       => "hiring_request",
		};

		// получаем заголовок и тело для пуша
		$push_title        = self::_getPushTitle($message_type, $parent_type, $sender_member_info, $additional_data);
		$push_title_locale = Type_Thread_Message_Main::getHandler($message)::getPushTitleLocale($message);
		$push_body         = Type_Thread_Message_Main::getHandler($message)::getPushBody($message);

		// получаем флаг нужно ли форсить отправку пуша
		$is_need_force_push = Type_Thread_Message_Main::getHandler($message)::isNeedForcePush($message);
		$is_need_force_push = $is_need_force_push === true ? 1 : 0;

		$sender_user_type = !is_null($sender_member_info) ? Main::USER_TYPE_SCHEMA[Main::getUserType($sender_member_info->npc_type)] : "";

		// собираем ответ
		$output = [
			"badge_inc_count"    => 0,
			"push_type"          => 1,
			"event_type"         => $event_type,
			"text_push"          => [
				"company_id"        => COMPANY_ID,
				"title"             => $push_title,
				"body"              => $push_body,
				"body_localization" => $push_body_locale,
				"sender_user_id"    => $sender_user_id,
				"thread_map"        => $thread_map,
				"parent_key"        => \CompassApp\Pack\Thread::doEncrypt($thread_map),
				"collapse_id"       => sha1(\CompassApp\Pack\Message::doEncrypt($message_map)),
				"parent_id"         => $parent_id,
				"parent_type"       => $parent_name_type,
				"entity_type"       => "thread_message",
				"entity_data"       => [
					"message_type"     => Apiv1_Format::getThreadMessageOutputType($message_type),
					"sender_user_type" => $sender_user_type,
				],
			],
			"is_need_force_push" => $is_need_force_push,
		];

		// если имеется локализация тайтла, то добавляем в ответ
		if (count($push_title_locale) > 0) {
			$output["text_push"]["title_localization"] = $push_title_locale;
		}

		return $output;
	}

	/**
	 * Получаем заголовок для пуша
	 *
	 * @param int               $message_type
	 * @param int               $parent_type
	 * @param MemberStruct|null $sender_member_info
	 * @param array             $additional_data
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	protected static function _getPushTitle(int $message_type, int $parent_type, MemberStruct|null $sender_member_info, array $additional_data):string {

		if (!is_null($sender_member_info)) {
			return $sender_member_info->full_name;
		}

		// если это не системное сообщение или не относится к заявкам найма/увольнения, то ругаемся
		if ($message_type != THREAD_MESSAGE_TYPE_SYSTEM || !in_array($parent_type, [PARENT_ENTITY_TYPE_DISMISSAL_REQUEST, PARENT_ENTITY_TYPE_HIRING_REQUEST])) {
			throw new ParseFatalException("no member data for user!");
		}

		return $additional_data["full_name"] ?? "";
	}
}
