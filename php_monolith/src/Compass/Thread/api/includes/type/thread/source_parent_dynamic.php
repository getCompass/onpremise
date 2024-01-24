<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с dynamic данными source_parent_rel сущности треда
 */
class Type_Thread_SourceParentDynamic {

	// подтипы синглов-диалогов (в случае изменения обязательно продублировать в php_conversation!!!)
	protected const _SINGLE_SUBTYPES = [
		CONVERSATION_TYPE_SINGLE_DEFAULT,
		CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT,
		CONVERSATION_TYPE_SINGLE_NOTES,
	];

	// подтипы групповых диалогов (в случае изменения обязательно продублировать в php_conversation!!!)
	protected const _GROUP_SUBTYPES = [
		CONVERSATION_TYPE_GROUP_DEFAULT,
		CONVERSATION_TYPE_GROUP_HIRING,
		CONVERSATION_TYPE_GROUP_GENERAL,
		CONVERSATION_TYPE_GROUP_SUPPORT,
		CONVERSATION_TYPE_GROUP_RESPECT,
	];

	// подтип публичных диалогов, доступ к которым имеют все пользователи компании (в случае изменения обязательно продублировать в php_conversation!!!)
	protected const _PUBLIC_SUBTYPES = [
		CONVERSATION_TYPE_PUBLIC_DEFAULT,
	];

	// типы родительских сущностей для треда в формате string
	public const CONVERSATION_TYPE_SINGLE = "conversation_single"; // сингл диалог
	public const CONVERSATION_TYPE_GROUP  = "conversation_group";  // групповой диалог
	public const CONVERSATION_TYPE_PUBLIC = "conversation_public"; // публичный диалог

	// получаем source_parent_rel_dynamic на основе source_parent_rel из thread_meta
	public static function get(array $source_parent_rel):array {

		$source_parent_map  = Type_Thread_SourceParentRel::getMap($source_parent_rel);
		$source_parent_type = Type_Thread_SourceParentRel::getType($source_parent_rel);
		[$location_type, $user_clear_info, $user_mute_info, $conversation_clear_info] = match ($source_parent_type) {
			SOURCE_PARENT_ENTITY_TYPE_CONVERSATION => Gateway_Socket_Conversation::getDynamic($source_parent_map),
			SOURCE_PARENT_ENTITY_TYPE_THREAD => [],
		};

		return [
			new Struct_SourceParentRel_Dynamic(
				Type_Thread_SourceParentDynamic::getLocationTypeParentString($location_type),
				$user_clear_info,
				$user_mute_info,
				$conversation_clear_info
			),
			$location_type == CONVERSATION_TYPE_GROUP_HIRING,
			$location_type == CONVERSATION_TYPE_GROUP_SUPPORT,
			$location_type,
		];
	}

	// является ли тип подтипом сингл-диалога
	public static function isSubtypeOfSingle(int $location_type):bool {

		return in_array($location_type, self::_SINGLE_SUBTYPES);
	}

	// является ли тип подтипом группового диалога
	public static function isSubtypeOfGroup(int $location_type):bool {

		return in_array($location_type, self::_GROUP_SUBTYPES);
	}

	// является ли тип подтипом публичных диалогов (Личный Heroes ...)
	public static function isSubtypeOfPublicGroup(int $location_type):bool {

		return in_array($location_type, self::_PUBLIC_SUBTYPES);
	}

	// является ли тип группового диалога
	public static function isConversationTypeGroup(string $location_type):bool {

		return $location_type == self::CONVERSATION_TYPE_GROUP;
	}

	// получаем тип места нахождения сущности к которой прикреплен тред в string
	public static function getLocationTypeParentString(int $location_type):string {

		if (self::isSubtypeOfSingle($location_type)) {
			return self::CONVERSATION_TYPE_SINGLE;
		}

		if (self::isSubtypeOfGroup($location_type)) {
			return self::CONVERSATION_TYPE_GROUP;
		}

		if (self::isSubtypeOfPublicGroup($location_type)) {
			return self::CONVERSATION_TYPE_PUBLIC;
		}

		throw new ParseFatalException("Unsupported location_type: {$location_type}");
	}

	// получить clear_info_until для пользователя
	public static function getClearInfoUntil(Struct_SourceParentRel_Dynamic $source_parent_rel_dynamic, int $user_id):int {

		$clear_until = 0;
		if (isset($source_parent_rel_dynamic->user_clear_info[$user_id])) {
			$clear_until = $source_parent_rel_dynamic->user_clear_info[$user_id]["clear_until"] ?? 0;
		}
		if (isset($source_parent_rel_dynamic->conversation_clear_info[$user_id])) {

			$conversation_clear_until = $source_parent_rel_dynamic->conversation_clear_info[$user_id]["clear_until"] ?? 0;
			if ($conversation_clear_until > $clear_until) {
				$clear_until = $conversation_clear_until;
			}
		}
		return $clear_until;
	}

	// получить user_mute_info для пользователя
	public static function isUserMuteMeta(Struct_SourceParentRel_Dynamic $source_parent_rel_dynamic, int $user_id):int {

		// актуализируем data
		$user_mute_info = $source_parent_rel_dynamic->user_mute_info[$user_id] ?? 0;

		return $user_mute_info == 1;
	}
}