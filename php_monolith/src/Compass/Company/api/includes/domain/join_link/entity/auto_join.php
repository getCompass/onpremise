<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с ссылками-приглашений для автоматического вступлению в компанию
 * @package Compass\Company
 */
class Domain_JoinLink_Entity_AutoJoin {

	/** дефолтные параметры для создаваемой ссылки – устанавливаем без ограничений */
	protected const _LIVES_DAY_COUNT = 0;
	protected const _CAN_USE_COUNT   = 0;

	/** существующие типы auto join */
	public const TYPE_AUTO_JOIN_AS_MEMBER       = 1;
	public const TYPE_AUTO_JOIN_AS_GUEST        = 2;
	public const TYPE_AUTO_JOIN_WITH_MODERATION = 3;

	/**
	 * конвертируем строковый тип во внутреннюю константу
	 *
	 * @return int
	 * @throws ParseFatalException
	 */
	public static function convertStringifyType(string $type):int {

		return match ($type) {
			"member"     => self::TYPE_AUTO_JOIN_AS_MEMBER,
			"guest"      => self::TYPE_AUTO_JOIN_AS_GUEST,
			"moderation" => self::TYPE_AUTO_JOIN_WITH_MODERATION,
			default      => throw new ParseFatalException("unexpected type [$type]")
		};
	}

	/**
	 * получаем ссылку-приглашение конкретного типа
	 *
	 * @return Struct_Db_CompanyData_JoinLink
	 * @throws cs_JoinLinkNotExist
	 */
	public static function get(int $type):Struct_Db_CompanyData_JoinLink {

		$join_link_uniq = Type_System_Datastore::get(self::_getKey($type))["join_link_uniq"] ?? "";
		if ($join_link_uniq == "") {
			throw new cs_JoinLinkNotExist();
		}

		// достаем ссылку из базы
		$join_link = Domain_JoinLink_Action_Get::do($join_link_uniq);

		// если entry_option ссылки-приглашения не соответствует типу
		// то считаем что ссылки нет
		if ($join_link->entry_option !== self::_resolveEntryOptionByType($type)) {
			throw new cs_JoinLinkNotExist();
		}

		// если у ссылки были отредактирован лимит на вступление и срок жизни
		if ($join_link->expires_at !== 0 || $join_link->can_use_count !== self::_CAN_USE_COUNT) {
			throw new cs_JoinLinkNotExist();
		}

		return $join_link;
	}

	/**
	 * формируем ключ для получения ссылки-приглашения конкретного типа
	 *
	 * @return string
	 */
	protected static function _getKey(int $type):string {

		return __CLASS__ . "_" . $type;
	}

	/**
	 * создаем ссылку-приглашения
	 *
	 * @return Struct_Db_CompanyData_JoinLink
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws cs_ExceededCountActiveInvite
	 */
	public static function create(int $creator_user_id, int $type):Struct_Db_CompanyData_JoinLink {

		// определяем поведение ссылки-приглашения
		$entry_option = self::_resolveEntryOptionByType($type);

		// создаем приглашение
		$join_link = Domain_JoinLink_Action_Create_Regular::do($creator_user_id, self::_LIVES_DAY_COUNT, false, self::_CAN_USE_COUNT, $entry_option, ignore_limit: true);

		// сохраняем ссылку в базу
		Type_System_Datastore::set(self::_getKey($type), ["join_link_uniq" => $join_link->join_link_uniq]);

		return $join_link;
	}

	/**
	 * определяем entry_option на основе типа auto-join ссылки
	 *
	 * @return int
	 */
	protected static function _resolveEntryOptionByType(int $type):int {

		return match ($type) {
			self::TYPE_AUTO_JOIN_AS_MEMBER       => Domain_JoinLink_Entity_Main::ENTRY_OPTION_JOIN_AS_MEMBER,
			self::TYPE_AUTO_JOIN_AS_GUEST        => Domain_JoinLink_Entity_Main::ENTRY_OPTION_JOIN_AS_GUEST,
			self::TYPE_AUTO_JOIN_WITH_MODERATION => Domain_JoinLink_Entity_Main::ENTRY_OPTION_NEED_POSTMODERATION,
		};
	}
}