<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс работает с данными компании
 */
class Type_Company_Default {

	public const GENERAL      = "general";
	public const HEROES       = "heroes";
	public const CHALLENGE    = "challenge";
	public const RESPECT      = "respect";
	public const EXACTINGNESS = "exactingness";
	public const ACHIEVEMENT  = "achievement";
	public const HIRING       = "hiring";

	public const CONVERSATION_KEY_BY_ENTITY = [
		self::GENERAL      => Domain_Company_Entity_Config::GENERAL_CONVERSATION_KEY_NAME,
		self::HEROES       => Domain_Company_Entity_Config::HEROES_CONVERSATION_KEY_NAME,
		self::CHALLENGE    => Domain_Company_Entity_Config::CHALLENGE_CONVERSATION_KEY_NAME,
		self::RESPECT      => Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME,
		self::EXACTINGNESS => Domain_Company_Entity_Config::EXACTINGNESS_CONVERSATION_KEY_NAME,
		self::ACHIEVEMENT  => Domain_Company_Entity_Config::ACHIEVEMENT_CONVERSATION_KEY_NAME,
		self::HIRING       => Domain_Company_Entity_Config::HIRING_CONVERSATION_KEY_NAME,
	];

	/**
	 * получаем conversation_map компании по типу нужного диалога
	 *
	 * @throws \parseException
	 */
	public static function getCompanyGroupConversationMap(string $type):string {

		if (!isset(self::CONVERSATION_KEY_BY_ENTITY[$type])) {

			throw new ParseFatalException("unknown param type = {$type}");
		}

		$key = self::CONVERSATION_KEY_BY_ENTITY[$type];

		$value = Domain_Conversation_Action_Config_Get::do($key);
		return $value["value"] ?? "";
	}

	/**
	 * получаем conversation_map компании по ключу нужного диалога
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getCompanyGroupConversationMapByKey(string $key):string {

		$value = Domain_Conversation_Action_Config_Get::do($key);

		if (!isset($value["value"])) {
			throw new \cs_RowIsEmpty();
		}

		return $value["value"];
	}

	/**
	 * Проверяет, включен ли диалог в список по-умолчнию
	 *
	 */
	public static function checkIsDefaultGroupOnAddMember(string $conversation_map):bool {

		$default_group_list_on_add_member = Domain_Group_Entity_Company::getDefaultGroupList();

		foreach ($default_group_list_on_add_member as $default_group_list_on_add_member_item) {

			$value = Domain_Conversation_Action_Config_Get::do($default_group_list_on_add_member_item);

			if ($value["value"] === $conversation_map) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Проверяем является ли диалог группой general
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function isGeneralGroup(string $conversation_map):bool {

		// получаем ключ диалога general
		$general_conversation_map = Type_Company_Default::getCompanyGroupConversationMapByKey(Domain_Company_Entity_Config::GENERAL_CONVERSATION_KEY_NAME);

		if ($conversation_map === $general_conversation_map) {
			return true;
		}

		return false;
	}
}