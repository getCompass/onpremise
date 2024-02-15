<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\System\Locale;

/**
 * класс для групп компании
 */
class Domain_Group_Entity_Company {

	// список дефолтных групп для создателя компании
	public const DEFAULT_GROUP_LIST_ON_COMPANY_CREATOR = [
		Domain_Company_Entity_Config::GENERAL_CONVERSATION_KEY_NAME,
		Domain_Company_Entity_Config::HIRING_CONVERSATION_KEY_NAME,
		Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME,
	];

	// список дефолтных групп при добавлении участника в компанию
	public const DEFAULT_GROUP_LIST_ON_ADD_MEMBER = [
		Domain_Company_Entity_Config::GENERAL_CONVERSATION_KEY_NAME,
		Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME,
	];

	// список групп найма и увольнения для добавления пользователя в нее
	public const HIRING_GROUP_LIST_ON_ADD_MEMBER = [
		Domain_Company_Entity_Config::HIRING_CONVERSATION_KEY_NAME,
	];

	// список дефолтных групп для расширенной карточки
	public const EXTENDED_GROUP_LIST = [
		Domain_Company_Entity_Config::HEROES_CONVERSATION_KEY_NAME,
		Domain_Company_Entity_Config::CHALLENGE_CONVERSATION_KEY_NAME,
		Domain_Company_Entity_Config::EXACTINGNESS_CONVERSATION_KEY_NAME,
		Domain_Company_Entity_Config::ACHIEVEMENT_CONVERSATION_KEY_NAME,
	];

	// список групп найма и увольнения
	public const HIRING_GROUP_LIST_ON_CREATE_COMPANY = [
		Domain_Company_Entity_Config::HIRING_CONVERSATION_KEY_NAME,
	];

	// имена дефолтных групп компании
	protected const _DEFAULT_GROUP_LOCALE_LIST_BY_KEY = [
		Domain_Company_Entity_Config::GENERAL_CONVERSATION_KEY_NAME      => "general",
		Domain_Company_Entity_Config::HEROES_CONVERSATION_KEY_NAME       => "heroes",
		Domain_Company_Entity_Config::CHALLENGE_CONVERSATION_KEY_NAME    => "challenge",
		Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME      => "respect",
		Domain_Company_Entity_Config::EXACTINGNESS_CONVERSATION_KEY_NAME => "exactingness",
		Domain_Company_Entity_Config::ACHIEVEMENT_CONVERSATION_KEY_NAME  => "achievement",
		Domain_Company_Entity_Config::HIRING_CONVERSATION_KEY_NAME       => "hiring",
		Domain_Company_Entity_Config::NOTES_CONVERSATION_KEY_NAME        => "notes",
		Domain_Company_Entity_Config::SUPPORT_CONVERSATION_KEY_NAME      => "support",
	];

	// список групп, находящиеся в избранном при вступлении
	public const FAVORITE_DEFAULT_GROUP_LIST_ON_JOIN = [
		Domain_Company_Entity_Config::HIRING_CONVERSATION_KEY_NAME,
		Domain_Company_Entity_Config::NOTES_CONVERSATION_KEY_NAME,
		Domain_Company_Entity_Config::SUPPORT_CONVERSATION_KEY_NAME,
		Domain_Company_Entity_Config::GENERAL_CONVERSATION_KEY_NAME,
	];

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Получаем название дефолтной группы по ключу
	 *
	 * @param string $key
	 * @param string $locale
	 *
	 * @return string
	 * @throws LocaleTextNotFound
	 */
	public static function getDefaultGroupNameByKey(string $key, string $locale):string {

		try {
			return Locale::getText(getConfig("LOCALE_TEXT"), "conversation_name", self::_DEFAULT_GROUP_LOCALE_LIST_BY_KEY[$key], $locale);
		} catch (LocaleTextNotFound) {

			return Locale::getText(
				getConfig("LOCALE_TEXT"), "conversation_name", self::_DEFAULT_GROUP_LOCALE_LIST_BY_KEY[$key], Locale::LOCALE_ENGLISH);
		}
	}

	/**
	 * получаем текущий список дефолтных групп при добавлении
	 *
	 * @return string[]
	 */
	public static function getDefaultGroupList(bool $is_only_card = false):array {

		$default_group_list = Domain_Group_Entity_Company::DEFAULT_GROUP_LIST_ON_ADD_MEMBER;

		// если пока не надо автоматически всегда создавать чат спасибо - возвращаем в дефолтной как было
		if (!IS_NEED_CREATE_RESPECT_CONVERSATION) {

			foreach ($default_group_list as $k => $v) {

				// убираем чат Спасибо
				if ($v == Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME) {

					unset($default_group_list[$k]);
					break;
				}
			}
		}

		if (Domain_Company_Action_Config_GetExtendedEmployeeCard::do()) {

			if ($is_only_card) {

				$extended_group_list = Domain_Group_Entity_Company::EXTENDED_GROUP_LIST;

				// если пока не надо автоматически всегда создавать чат спасибо - возвращаем в расширенную как было
				if (!IS_NEED_CREATE_RESPECT_CONVERSATION) {
					$extended_group_list[] = Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME;
				}
				return $extended_group_list;
			}
			$default_group_list = array_merge($default_group_list, Domain_Group_Entity_Company::EXTENDED_GROUP_LIST);

			// если пока не надо автоматически всегда создавать чат спасибо - возвращаем в расширенную как было
			if (!IS_NEED_CREATE_RESPECT_CONVERSATION) {
				$default_group_list[] = Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME;
			}
		}

		return $default_group_list;
	}

	/**
	 * нужна ли дефолтная группа в избранном у пользователя
	 */
	public static function isNeedToFavorite(string $key):bool {

		return in_array($key, self::FAVORITE_DEFAULT_GROUP_LIST_ON_JOIN);
	}

	/**
	 * получаем список дефолтных групп для пользователя
	 */
	public static function getDefaultGroupKeyList(bool $is_owner):array {

		$group_list = self::DEFAULT_GROUP_LIST_ON_ADD_MEMBER;

		// для создателя компании свой список дефолтных групп
		if ($is_owner === true) {
			$group_list = self::DEFAULT_GROUP_LIST_ON_COMPANY_CREATOR;
		}

		// если пока не надо автоматически добавлять в чат спасибо - возвращаем в дефолтной как было
		if (!IS_NEED_CREATE_RESPECT_CONVERSATION) {

			$key = array_search(Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME, $group_list, true);
			if ($key !== false) {
				unset($group_list[$key]);
			}
		}

		return $group_list;
	}
}
