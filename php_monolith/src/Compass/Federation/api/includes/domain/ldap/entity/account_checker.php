<?php

namespace Compass\Federation;

class Domain_Ldap_Entity_AccountChecker {

	/** Флаг, указывающий на то что учетная запись в Active Directory заблокирована */
	protected const _ACTIVE_DIRECTORY_DISABLED_ACCOUNT_FLAG = 2;

	/**
	 * фильтруем список связей «учетная запись LDAP» <–> «Compass пользователей» и определяем список отключенных и удаленных LDAP аккаунтов со связями
	 *
	 * @param Struct_Db_LdapData_LdapAccountUserRel[] $account_user_rel_list
	 * @param array                                   $found_entry_list
	 *
	 * @return array
	 */
	public static function filterAccountList(array $account_user_rel_list, array $found_entry_list):array {

		// оставим в списке только активные связи
		$account_user_rel_list = array_filter($account_user_rel_list, static fn(Struct_Db_LdapData_LdapAccountUserRel $account_user_rel) => $account_user_rel->status == Domain_Ldap_Entity_AccountUserRel::STATUS_ACTIVE);

		// из списка связей сделаем словарь, чтобы можно было быстрей получить нужную запись по uid
		$account_user_rel_map = array_column($account_user_rel_list, null, "uid");

		// из списка учетных записей сделаем словарь, чтобы можно было быстрей получить нужную запись по uid
		$found_entry_map = array_column($found_entry_list, null, mb_strtolower(Domain_Ldap_Entity_Config::getUserUniqueAttribute()));

		// сюда сложим аккаунты, которые были отключены в LDAP каталоге
		$disabled_account_user_rel_list = [];

		// сюда сложим аккаунты, которые были удалены из LDAP каталога (не были найдены)
		$deleted_account_user_rel_list = [];

		// ищем отключенные аккаунты
		foreach ($found_entry_map as $uid => $entry) {

			// если для такого аккаунта не существует связи в приложении, то пропускаем
			if (!isset($account_user_rel_map[$uid])) {
				continue;
			}

			// если аккаунт отключили
			if (self::isDisabledAccount($entry)) {
				$disabled_account_user_rel_list[] = $account_user_rel_map[$uid];
			}
		}

		// ищем удаленные аккаунты
		foreach ($account_user_rel_map as $uid => $account_user_rel) {

			if (!isset($found_entry_map[$uid])) {
				$deleted_account_user_rel_list[] = $account_user_rel;
			}
		}

		return [$disabled_account_user_rel_list, $deleted_account_user_rel_list];
	}

	/**
	 * является ли учетная запись заблокированной
	 *
	 * @return bool
	 */
	public static function isDisabledAccount(array $ldap_entry):bool {

		// проверяем наличие атрибута pwdAccountLockedTime
		if (isset($ldap_entry["pwdaccountlockedtime"])) {
			return true;
		}

		// проверяем по атрибуту accountStatus
		if (isset($ldap_entry["accountstatus"]) && $ldap_entry["accountstatus"] === "inactive") {
			return true;
		}

		// проверяем по атрибуту nsAccountLock
		if (isset($ldap_entry["nsaccountlock"]) && $ldap_entry["nsaccountlock"] === "TRUE") {
			return true;
		}

		// проверяем по атрибуту userAccountControl (active directory)
		if (isset($ldap_entry["useraccountcontrol"]) && ($ldap_entry["useraccountcontrol"] & self::_ACTIVE_DIRECTORY_DISABLED_ACCOUNT_FLAG) != 0) {
			return true;
		}

		return false;
	}
}