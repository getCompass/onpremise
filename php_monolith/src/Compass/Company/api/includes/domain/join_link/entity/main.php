<?php

namespace Compass\Company;

/**
 * Основной класс сущности
 */
class Domain_JoinLink_Entity_Main {

	public const STATUS_ACTIVE  = 1; // ссылка активна и доступна для использования
	public const STATUS_USED    = 2; // ссылка уже использована
	public const STATUS_DELETED = 3; // ссылка удалена

	// типы инвайтов
	public const TYPE_REGULAR = 10; // обычная ссылка
	public const TYPE_MAIN    = 11; // основная ссылка
	public const TYPE_SINGLE  = 12; // одиночная ссылка

	public const ENTRY_OPTION_JOIN_AS_MEMBER      = 0; // пользователь вступающий по ссылке – вступает в пространство как member
	public const ENTRY_OPTION_NEED_POSTMODERATION = 1; // пользователь вступающий по ссылке – ждет модерации заявки
	public const ENTRY_OPTION_JOIN_AS_GUEST       = 2; // пользователь вступающий по ссылке – вступает в пространство как guest

	// список доступных ENTRY_OPTION
	public const AVAILABLE_ENTRY_OPTION_LIST = [
		self::ENTRY_OPTION_JOIN_AS_MEMBER,
		self::ENTRY_OPTION_NEED_POSTMODERATION,
		self::ENTRY_OPTION_JOIN_AS_GUEST,
	];

	public const TYPE_SCHEMA_LEGACY = [
		self::TYPE_MAIN    => "main",
		self::TYPE_REGULAR => "regular",
	];
	public const TYPE_SCHEMA        = [
		self::TYPE_MAIN    => "main",
		self::TYPE_REGULAR => "regular",
		self::TYPE_SINGLE  => "single",
	];

	public const MAX_REGULAR_COUNT = 49; // максимальное количество обычных ссылок

	public const DEFAULT_MAIN_LIFE_DAY_COUNT_LEGACY = 7; // дефолтное ограничение времени работы ссылок (для старой версии)

	/**
	 * конвертим string-тип в нужный формат
	 *
	 * @throws cs_IncorrectType
	 */
	public static function convertStringToIntType(string $type):int {

		$to_type = array_flip(self::TYPE_SCHEMA);
		if (!isset($to_type[$type])) {
			throw new cs_IncorrectType();
		}
		return $to_type[$type];
	}

	/**
	 * конвертим int-тип в нужный формат
	 *
	 * @throws cs_IncorrectType
	 */
	public static function convertIntToStringType(int $type):string {

		$to_type = self::TYPE_SCHEMA;

		if (!isset($to_type[$type])) {
			throw new cs_IncorrectType();
		}
		return $to_type[$type];
	}

	// получаем expire секунд из количества дней
	public static function getLiveTimeByDayCount(int $lives_day_count):int {

		return $lives_day_count * DAY1;
	}

	// получаем expire секунд из количества часов
	public static function getLiveTimeByHourCount(int $lives_hour_count):int {

		return $lives_hour_count * HOUR1;
	}

	/**
	 * генерируем инвайт ссылку
	 */
	public static function getLink(Struct_Db_CompanyData_JoinLink $join_link):string {

		return WEB_PROTOCOL_PUBLIC . "://" . PUBLIC_ADDRESS_GLOBAL . "/join/" . $join_link->join_link_uniq . "/";
	}

	/**
	 * Выбрасываем исключение если ссылку нельзя использовать
	 *
	 * @throws cs_InviteLinkIdExpired
	 * @throws cs_InviteLinkNotActive
	 */
	public static function assertCanUse(Struct_Db_CompanyData_JoinLink $join_link):void {

		// если ссылка не ограничена по времени и время её жизни истекло
		if (!Domain_JoinLink_Entity_Main::isLinkWithoutExpiresLimit($join_link) && $join_link->expires_at < time()) {
			throw new cs_InviteLinkIdExpired();
		}

		// если закончились все попытки для использования ссылки
		if ($join_link->status === Domain_JoinLink_Entity_Main::STATUS_USED && $join_link->can_use_count < 1) {
			throw new cs_InviteLinkNotActive();
		}

		if ($join_link->status !== Domain_JoinLink_Entity_Main::STATUS_ACTIVE) {
			throw new cs_InviteLinkNotActive("invite-link is declined");
		}
	}

	/**
	 * получаем лимит для активных обычных ссылок-инвайтов
	 */
	public static function getRegularMaxCount():int {

		// если выполняется НЕ на тестовом сервере
		if (isTestServer() === false) {
			return self::MAX_REGULAR_COUNT;
		}

		$test_mass_invite_link_limit = Type_System_Testing::getForceRegularInviteLinkLimit();

		// если передан тестовый лимит
		if ($test_mass_invite_link_limit > 0) {
			return $test_mass_invite_link_limit;
		}

		return self::MAX_REGULAR_COUNT;
	}

	/**
	 * ссылка без лимита на количество использований?
	 */
	public static function isLinkWithoutCanUseLimit(Struct_Db_CompanyData_JoinLink $join_link):bool {

		return $join_link->status === Domain_JoinLink_Entity_Main::STATUS_ACTIVE && $join_link->can_use_count === 0;
	}

	/**
	 * ссылка без лимита ограничения по времени?
	 */
	public static function isLinkWithoutExpiresLimit(Struct_Db_CompanyData_JoinLink $join_link):bool {

		return $join_link->expires_at === 0;
	}

	/**
	 * Выбрасываем исключение, если ссылка с лимитом ограничения по времени
	 */
	public static function assertLinkCanBeEdited(Struct_Db_CompanyData_JoinLink $join_link):void {

		if (!self::isLinkWithoutExpiresLimit($join_link) && $join_link->expires_at < time()) {
			throw new cs_InvalidStatusForEditInvite();
		}
	}

	/**
	 * Выбрасываем исключение, если ссылка удалена
	 */
	public static function assertLinkIsNotDeleted(Struct_Db_CompanyData_JoinLink $join_link):void {

		if ($join_link->status == Domain_JoinLink_Entity_Main::STATUS_DELETED) {
			throw new cs_JoinLinkDeleted();
		}
	}

	/**
	 * Конвертируем deprecated флаг is_postmoderation в entry_option
	 *
	 * @return int
	 */
	public static function convertPostModerationFlagToEntryOption(bool $is_postmoderation):int {

		return $is_postmoderation ? self::ENTRY_OPTION_NEED_POSTMODERATION : self::ENTRY_OPTION_JOIN_AS_MEMBER;
	}

	/**
	 * Конвертируем entry_option в deprecated флаг is_postmoderation
	 *
	 * @return bool
	 */
	public static function isPostModerationEnabled(int $entry_option):bool {

		return $entry_option === self::ENTRY_OPTION_NEED_POSTMODERATION;
	}

	/**
	 * Получаем role нового пользователя пространства по entry_option
	 *
	 * @return int
	 * @throws \parseException
	 */
	public static function resolveRole(int $entry_option):int {

		return match ($entry_option) {
			self::ENTRY_OPTION_JOIN_AS_MEMBER => \CompassApp\Domain\Member\Entity\Member::ROLE_MEMBER,
			self::ENTRY_OPTION_JOIN_AS_GUEST => \CompassApp\Domain\Member\Entity\Member::ROLE_GUEST,
			default => throw new \parseException("unexpected entry_option: $entry_option"),
		};
	}
}
