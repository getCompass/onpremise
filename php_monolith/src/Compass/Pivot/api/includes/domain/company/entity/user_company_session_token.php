<?php

namespace Compass\Pivot;

/**
 * Class Domain_Company_Entity_UserCompanySessionToken
 */
class Domain_Company_Entity_UserCompanySessionToken {

	protected const _USER_COMPANY_SESSION_TOKEN_STATUS_ACTIVE   = 1;
	protected const _USER_COMPANY_SESSION_TOKEN_STATUS_INACTIVE = 2;

	/**
	 * создаем токен
	 *
	 * @throws \queryException
	 */
	public static function create(int $user_id, string $session_uniq, int $company_id):string {

		$user_company_session_token = generateUUID();
		$status                     = self::_USER_COMPANY_SESSION_TOKEN_STATUS_ACTIVE;
		$created_at                 = time();

		Gateway_Db_PivotUser_UserCompanySessionTokenList::insert($user_company_session_token, $user_id, $session_uniq, $status, $company_id, $created_at);
		return $user_company_session_token;
	}

	/**
	 *
	 *
	 * @throws cs_InvalidUserCompanySessionToken|\parseException
	 */
	public static function assert(int $user_id, int $company_id, string $user_company_session_token):void {

		try {

			// получаем токен из базы
			$user_company_session_token_row = Gateway_Db_PivotUser_UserCompanySessionTokenList::getOne($user_id, $user_company_session_token);
		} catch (\cs_RowIsEmpty) {
			throw new cs_InvalidUserCompanySessionToken();
		}

		// проверяем что он принадлежит нужной компании
		if ($user_company_session_token_row->company_id != $company_id) {
			throw new cs_InvalidUserCompanySessionToken();
		}

		if ($user_company_session_token_row->status != self::_USER_COMPANY_SESSION_TOKEN_STATUS_ACTIVE) {
			throw new cs_InvalidUserCompanySessionToken();
		}

		// токены одноразовые
		$set = [
			"updated_at" => time(),
			"status"     => self::_USER_COMPANY_SESSION_TOKEN_STATUS_INACTIVE,
		];
		Gateway_Db_PivotUser_UserCompanySessionTokenList::set($user_id, $user_company_session_token, $set);
	}

	/**
	 * помечаем неактивным и получаем помеченные по сессии
	 *
	 * @return Struct_Db_PivotUser_UserCompanySessionToken[]
	 */
	public static function setInactiveAndGetBySessionUniq(int $user_id, string $session_uniq):array {

		// помечаем неактивными
		$set = [
			"updated_at" => time(),
			"status"     => self::_USER_COMPANY_SESSION_TOKEN_STATUS_INACTIVE,
		];
		Gateway_Db_PivotUser_UserCompanySessionTokenList::setBySessionUniq($user_id, $session_uniq, $set);

		// получаем
		return Gateway_Db_PivotUser_UserCompanySessionTokenList::getBySessionUniq($user_id, $session_uniq);
	}

	/**
	 * помечаем неактивными и получаем помеченные по списку сессий
	 *
	 * @return Struct_Db_PivotUser_UserCompanySessionToken[]
	 */
	public static function setInactiveAndGetBySessionUniqList(int $user_id, array $session_uniq_list):array {

		// помечаем неактивными
		$set = [
			"updated_at" => time(),
			"status"     => self::_USER_COMPANY_SESSION_TOKEN_STATUS_INACTIVE,
		];
		Gateway_Db_PivotUser_UserCompanySessionTokenList::setBySessionUniqList($user_id, $session_uniq_list, $set);

		// получаем
		return Gateway_Db_PivotUser_UserCompanySessionTokenList::getBySessionUniqList($user_id, $session_uniq_list);
	}
}