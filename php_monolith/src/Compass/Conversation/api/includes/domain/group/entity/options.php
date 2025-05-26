<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Request\ParamException;

/**
 * Сущность для работы с опциями группы
 */
class Domain_Group_Entity_Options {

	/**
	 * Проверяем ограничения в канале
	 *
	 * @throws Domain_Group_Exception_NotEnoughRights
	 * @throws ParamException
	 */
	public static function checkChannelRestrictionByConversationMap(int $user_id, string $conversation_map):void {

		// получаем мета данные группы
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// проверяем опции
		self::checkChannelRestrictionByMetaRow($user_id, $meta_row);
	}

	/**
	 * Проверяем ограничения в канале
	 *
	 * @throws \cs_UnpackHasFailed
	 * @throws Domain_Group_Exception_NotEnoughRights
	 * @throws ParamException
	 */
	public static function checkChannelRestrictionByMessageMap(int $user_id, string $message_map):void {

		// если сообщение не из диалога
		if (!\CompassApp\Pack\Message::isFromConversation($message_map)) {
			throw new ParamException("the message is not from conversation");
		}

		// получаем мету диалога
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);

		// проверяем опции
		self::checkChannelRestrictionByMetaRow($user_id, $meta_row);
	}

	/**
	 * Проверяем ограничения в канале сразу по мете
	 *
	 * @throws Domain_Group_Exception_NotEnoughRights
	 */
	public static function checkChannelRestrictionByMetaRow(int $user_id, array $meta_row):void {

		// проверяем что это группа, если нет, то выходим
		if (!Type_Conversation_Meta::isSubtypeOfGroup($meta_row["type"])) {
			return;
		}

		// проверяем что это канал, выходим если нет, то выходим
		if (!Type_Conversation_Meta_Extra::isChannel($meta_row["extra"])) {
			return;
		}

		// проверяем что пользователь есть в группе, если нет - то просто выходим, логика not member должна в другом месте обрабатываться
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {
			return;
		}

		// проверяем что пользователь админ в канале, если да, то выходим - ему можно
		if (Type_Conversation_Meta_Users::isGroupAdmin($user_id, $meta_row["users"])) {
			return;
		}

		// если дошли до сюда, то бросаем исключение, что прав нет
		throw new Domain_Group_Exception_NotEnoughRights("not enough rights");
	}

	/**
	 * Проверяем ограничения на реакции
	 */
	public static function checkReactionRestrictionByConversationMessageMap(int $user_id, string $message_map):void {

		// если сообщение не из выходим
		if (!\CompassApp\Pack\Message::isFromConversation($message_map)) {
			return;
		}

		// получаем мету диалога
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);

		// проверяем что это группа, если нет, то выходим
		if (!Type_Conversation_Meta::isSubtypeOfGroup($meta_row["type"])) {
			return;
		}

		// проверяем что пользователь есть в группе, если нет - то просто выходим, логика not member должна в другом месте обрабатываться
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {
			return;
		}

		// проверяем ограничение включено, если нет - то выходим
		if (Type_Conversation_Meta_Extra::isReactionsEnabled($meta_row["extra"])) {
			return;
		}

		// если дошли до сюда, то бросаем исключение, что прав нет, и админам и участникам при ограничении нельзя
		throw new Domain_Group_Exception_NotEnoughRights("not enough rights");
	}
}
