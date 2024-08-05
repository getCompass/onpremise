<?php

namespace Compass\Federation;

/**
 * класс описывающий действие по переактивации связи если ранее LDAP аккаунт был заблокирован
 * @package Compass\Federation
 */
class Domain_Ldap_Action_ReactivateAccountRel {

	/**
	 * выполняем действие
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(Struct_Db_LdapData_LdapAccountUserRel $account_user_rel):void {

		// если связь активна, то ничего не делаем
		if ($account_user_rel->status === Domain_Ldap_Entity_AccountUserRel::STATUS_ACTIVE) {
			return;
		}

		// возвращаем возможность авторизовываться в приложении ранее заблокированному аккаунту
		Gateway_Socket_Pivot::unblockUserAuthentication($account_user_rel->user_id);

		// обновляем статус связи, помечая связь активной
		Domain_Ldap_Entity_AccountUserRel::setStatus($account_user_rel->uid, Domain_Ldap_Entity_AccountUserRel::STATUS_ACTIVE);
	}
}