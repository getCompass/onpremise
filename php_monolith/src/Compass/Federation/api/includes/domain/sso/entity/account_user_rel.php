<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowDuplicationException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы со связью «SSO аккаунт» – «Пользователь Compass»
 * @package Compass\Federation
 */
class Domain_Sso_Entity_AccountUserRel {

	/**
	 * получаем user_id пользователя, с которым связан SSO аккаунт
	 *
	 * @param string $sub_hash
	 *
	 * @return int
	 * @throws ParseFatalException
	 */
	public static function getUserID(string $sub_hash):int {

		try {
			return Gateway_Db_SsoData_SsoAccountUserRel::getOne($sub_hash)->user_id;
		} catch (RowNotFoundException) {
			return 0;
		}
	}

	/**
	 * создаем связь «SSO аккаунт» – «Пользователь Compass»
	 *
	 * @throws Domain_Sso_Exception_UserRelationship_AlreadyExists
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function create(string $sub_plain, int $user_id):void {

		$sso_account_user_rel = new Struct_Db_SsoData_SsoAccountUserRel(
			sub_hash: Domain_Sso_Entity_Oidc_Token::getSubHash($sub_plain),
			user_id: $user_id,
			sub_plain: $sub_plain,
			created_at: time(),
		);

		try {
			Gateway_Db_SsoData_SsoAccountUserRel::insert($sso_account_user_rel);
		} catch (RowDuplicationException) {
			throw new Domain_Sso_Exception_UserRelationship_AlreadyExists();
		}
	}
}