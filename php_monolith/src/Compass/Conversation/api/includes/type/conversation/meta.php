<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * класс для работы с meta диалога
 */
class Type_Conversation_Meta {

	// подтипы синглов-диалогов (в случае изменения обязательно продублировать в php_thread!!!)
	protected const _SINGLE_SUBTYPES = [
		CONVERSATION_TYPE_SINGLE_DEFAULT,
		CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT,
	];

	// подтипы синглов-диалогов, доступных для создания/добавления
	protected const _ALLOWED_SINGLE_SUBTYPES = [
		CONVERSATION_TYPE_SINGLE_DEFAULT,
	];

	// подтипы групповых диалогов (в случае изменения обязательно продублировать в php_thread!!!)
	protected const _GROUP_SUBTYPES = [
		CONVERSATION_TYPE_GROUP_DEFAULT,
		CONVERSATION_TYPE_GROUP_HIRING,
		CONVERSATION_TYPE_GROUP_GENERAL,
		CONVERSATION_TYPE_GROUP_SUPPORT,
		CONVERSATION_TYPE_GROUP_RESPECT,
	];

	// подтип публичных диалогов, доступ к которым имеют все пользователи компании (в случае изменения обязательно продублировать в php_thread!!!)
	protected const _PUBLIC_SUBTYPES = [
		CONVERSATION_TYPE_PUBLIC_DEFAULT,
	];

	/** @var array типы диалогов, которые не подлежат архивации */
	protected const _IGNORE_ARCHIVING = [
		CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT,
	];

	/* @section работа с поле type меты диалога */

	/**
	 * получить тип диалога
	 *
	 */
	public static function getType(array $conversation_meta):int {

		return $conversation_meta["type"];
	}

	// является ли тип диалога подтипом сингл-диалога
	public static function isSubtypeOfSingle(int $conversation_type):bool {

		return in_array($conversation_type, self::_SINGLE_SUBTYPES);
	}

	/**
	 * // является ли тип диалога диалогом с системным ботом
	 *
	 */
	public static function isSystemBotConversationType(int $conversation_type):bool {

		return $conversation_type == CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT;
	}

	// является ли тип диалога подтипом группового диалога
	public static function isSubtypeOfGroup(int $conversation_type):bool {

		return in_array($conversation_type, self::_GROUP_SUBTYPES);
	}

	// является ли тип диалога подтипом публичных диалогов (Личный Heroes ...)
	public static function isSubtypeOfPublicGroup(int $conversation_type):bool {

		return in_array($conversation_type, self::_PUBLIC_SUBTYPES);
	}

	/**
	 * является ли тип диалога сингл-диалогом с пользователем
	 */
	public static function isDefaultSingleConversationType(int $conversation_type):bool {

		return $conversation_type == CONVERSATION_TYPE_SINGLE_DEFAULT;
	}

	/**
	 * является ли тип диалога чатом заметки
	 *
	 */
	public static function isNotesConversationType(int $conversation_type):bool {

		return $conversation_type == CONVERSATION_TYPE_SINGLE_NOTES;
	}

	/**
	 * Является ли тип чатом службы поддержки
	 */
	public static function isGroupSupportConversationType(int $conversation_type):bool {

		return $conversation_type === CONVERSATION_TYPE_GROUP_SUPPORT;
	}

	/**
	 * является ли тип диалога найма и увольнения
	 *
	 */
	public static function isHiringConversation(int $conversation_type):bool {

		return $conversation_type === CONVERSATION_TYPE_GROUP_HIRING;
	}

	// возвращает список всех подтипов сингл диалогов
	public static function getSingleSubtypes():array {

		return self::_SINGLE_SUBTYPES;
	}

	// возвращает список доступных сингл диалогов
	public static function getAllowedSingleSubtypes():array {

		return self::_ALLOWED_SINGLE_SUBTYPES;
	}

	// возвращает список всех подтипов групповых диалогов
	public static function getGroupSubtypes():array {

		return self::_GROUP_SUBTYPES;
	}

	/**
	 * Проверяем, что это single диалог с ботом
	 *
	 */
	public static function isSingleWithBot(array $meta_row):bool {

		// если это не single-диалог
		if (!self::isSubtypeOfSingle($meta_row["type"])) {
			return false;
		}

		// если в диалоге нет пользователей ботов
		$is_found = false;
		foreach ($meta_row["users"] as $user_id => $_) {
			$is_found &= Type_Conversation_Meta::isBot($meta_row["extra"], $user_id);
		}

		return $is_found;
	}

	/**
	 * Проверяет, можно ли заархивировать диалог указанного типа.
	 *
	 */
	public static function isAllowedForArchiving(int $conversation_type):bool {

		return !in_array($conversation_type, self::_IGNORE_ARCHIVING);
	}

	/* @section другое */

	/**
	 * есть ли переданный юзер в списка ботов диалога
	 *
	 */
	public static function isBot(array $conversation_extra, int $user_id):bool {

		return Type_Conversation_Meta_Extra::isBot($conversation_extra, $user_id);
	}

	/**
	 * получает диалог по первичному ключу
	 *
	 * @param string $conversation_map
	 *
	 * @return array
	 * @throws ParamException
	 */
	public static function get(string $conversation_map):array {

		try {
			return Gateway_Db_CompanyConversation_ConversationMetaLegacy::getOne($conversation_map);
		} catch (\cs_UnpackHasFailed|\cs_DecryptHasFailed) {
			throw new ParamException("try get meta from incorrect conversation_map");
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("conversation not found");
		}
	}

	// получает информацию о нескольких диалогах
	public static function getAll(array $conversation_map_list, bool $assoc_list = false):array {

		return Gateway_Db_CompanyConversation_ConversationMetaLegacy::getAll($conversation_map_list, $assoc_list);
	}

	// устанавливает роль пользователю
	// возвращает обновленный массив users
	public static function setUserRole(string $conversation_map, int $user_id, int $new_role):array {

		// открываем транзакцию
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::beginTransaction();

		// получаем запись на обновление
		$meta_row = Gateway_Db_CompanyConversation_ConversationMetaLegacy::getForUpdate($conversation_map);

		// меняем роль пользователю
		$meta_row["users"] = Type_Conversation_Meta_Users::changeMemberRole($user_id, $new_role, $meta_row["users"]);

		// обновляем запись
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::set($conversation_map, [
			"users"      => $meta_row["users"],
			"updated_at" => time(),
		]);

		// коммитим транзакцию
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::commitTransaction();

		return $meta_row["users"];
	}

	/**
	 * обновляем extra
	 *
	 * @param string $conversation_map
	 * @param int    $clear_until
	 *
	 * @throws ReturnFatalException
	 */
	public static function setConversationClearUntilForAll(string $conversation_map, int $clear_until):void {

		// открываем транзакцию
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::beginTransaction();

		// получаем запись на обновление
		$meta_row = Gateway_Db_CompanyConversation_ConversationMetaLegacy::getForUpdate($conversation_map);
		$extra    = Type_Conversation_Meta_Extra::setConversationClearUntilForAll($meta_row["extra"], $clear_until);

		// обновляем запись
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::set($conversation_map, [
			"extra"      => $extra,
			"updated_at" => time(),
		]);

		// коммитим транзакцию
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::commitTransaction();
	}
}