<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с таблицей user_conversation_rel
 */
class Type_UserConversation_UserConversationRel {

	// доступные типы диалогов пользователя
	protected const _ALLOW_CONVERSATION_TYPE_LIST = [
		CONVERSATION_TYPE_PUBLIC_DEFAULT,
		CONVERSATION_TYPE_SINGLE_NOTES,
	];

	// список типов диалогов по именам
	protected const _CONVERSATION_TYPE_BY_NAME_LIST = [
		CONVERSATION_TYPE_PUBLIC_DEFAULT => "public_heroes",
		CONVERSATION_TYPE_SINGLE_NOTES   => "notes",
	];

	/**
	 * добавляем запись в таблице
	 *
	 * @param int    $user_id
	 * @param int    $type
	 * @param string $conversation_map
	 *
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function add(int $user_id, int $type, string $conversation_map):void {

		if (!in_array($type, self::_ALLOW_CONVERSATION_TYPE_LIST)) {
			throw new ParseFatalException("not allowed this conversation type (type = $type)");
		}

		$insert = [
			"user_id"          => $user_id,
			"type"             => $type,
			"conversation_map" => $conversation_map,
			"created_at"       => time(),
		];
		Gateway_Db_CompanyConversation_MemberConversationTypeRel::insert($insert);
	}

	/**
	 * получаем запись по id пользователя и типу диалога
	 *
	 * @param int $user_id
	 * @param int $type
	 *
	 * @return Struct_Db_CompanyConversation_MemberConversationTypeRel
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(int $user_id, int $type):Struct_Db_CompanyConversation_MemberConversationTypeRel {

		return Gateway_Db_CompanyConversation_MemberConversationTypeRel::getByUserIdAndType($user_id, $type);
	}

	/**
	 * получаем тип диалога по его названию
	 *
	 * @param string $conversation_type_name
	 *
	 * @return int
	 * @throws ParseFatalException
	 */
	public static function getTypeByName(string $conversation_type_name):int {

		if (!in_array($conversation_type_name, self::_CONVERSATION_TYPE_BY_NAME_LIST)) {
			throw new ParseFatalException("not allowed this conversation type name (type_name = $conversation_type_name)");
		}

		return array_search($conversation_type_name, self::_CONVERSATION_TYPE_BY_NAME_LIST);
	}
}