<?php

namespace Compass\Federation;

use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с таблицей sso_data . auth_response_rel
 * @package Compass\Federation
 */
class Gateway_Db_SsoData_SsoAccountOidcTokenList extends Gateway_Db_SsoData_Main {

	protected const _TABLE_NAME = "sso_account_oidc_token_list";

	/**
	 * создаем запись
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_SsoData_SsoAccountOidcToken $sso_account_oidc_token):void {

		$insert_array = [
			"row_id"          => $sso_account_oidc_token->row_id,
			"sub_hash"        => $sso_account_oidc_token->sub_hash,
			"sso_auth_token"  => $sso_account_oidc_token->sso_auth_token,
			"expires_at"      => $sso_account_oidc_token->expires_at,
			"last_refresh_at" => $sso_account_oidc_token->last_refresh_at,
			"created_at"      => $sso_account_oidc_token->created_at,
			"updated_at"      => $sso_account_oidc_token->updated_at,
			"data"            => (array) $sso_account_oidc_token->data,
		];
		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_NAME, $insert_array, false);
	}

	/**
	 * получаем запись по sso_auth_token
	 *
	 * @return Struct_Db_SsoData_SsoAccountOidcToken
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws RowNotFoundException
	 */
	public static function getByAuthToken(string $sso_auth_token):Struct_Db_SsoData_SsoAccountOidcToken {

		// EXPLAIN `sso_auth_token` INDEX
		$query = "SELECT * FROM `?p` WHERE `sso_auth_token` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $sso_auth_token, 1);

		if (!isset($row["sso_auth_token"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_SsoData_SsoAccountOidcToken::rowToStruct($row);
	}
}