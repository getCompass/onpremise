<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Обновляем измененный username пользователя
 * @package Compass\Federation
 */
class Domain_Ldap_Action_UpdateAccountRelUsername {

	/**
	 * выполняем действие
	 *
	 * @param Struct_Db_LdapData_LdapAccountUserRel $account_user_rel
	 * @param Struct_Ldap_AccountData               $ldap_account_data
	 *
	 * @throws ParseFatalException
	 */
	public static function do(Struct_Db_LdapData_LdapAccountUserRel $account_user_rel, Struct_Ldap_AccountData $ldap_account_data):void {

		// если username совпадает, то ничего не делаем
		if ($account_user_rel->username === $ldap_account_data->username) {
			return;
		}

		Gateway_Db_LdapData_LdapAccountUserRel::set(
			$account_user_rel->uid,
			[
				"username"   => $ldap_account_data->username,
				"updated_at" => time(),
			]
		);
	}
}