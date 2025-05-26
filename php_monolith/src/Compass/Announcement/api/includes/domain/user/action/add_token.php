<?php declare(strict_types = 1);

namespace Compass\Announcement;

/**
 * Создает токен
 */
class Domain_User_Action_AddToken {

	/**
	 * Выполнить действие
	 *
	 * @param int    $user_id
	 * @param string $device_id
	 * @param array  $new_list_id_companies
	 * @param int    $expires_at
	 *
	 * @return string
	 * @throws \queryException
	 */
	public static function do(int $user_id, string $device_id, array $new_list_id_companies, int $expires_at):string {

		$current_list_id_companies = Gateway_Db_AnnouncementUser_UserCompany::getAllCompanyIdByUserId($user_id);
		$list_id_companies_to_add  = array_diff($new_list_id_companies, $current_list_id_companies);

		Gateway_Db_AnnouncementUser_UserCompany::deleteByExceptCompanyList($user_id, $new_list_id_companies);
		Gateway_Db_AnnouncementCompany_CompanyUser::deleteByExceptCompanyList($user_id, $new_list_id_companies);

		if ($list_id_companies_to_add !== []) {

			$user_company_list = [];

			foreach ($list_id_companies_to_add as $company_id) {

				$user_company_list[] = [
					"user_id"    => $user_id,
					"company_id" => $company_id,
					"expires_at" => $expires_at,
					"created_at" => time(),
					"updated_at" => time(),
				];
			}

			Gateway_Db_AnnouncementUser_UserCompany::insertList($user_company_list);
			Gateway_Db_AnnouncementCompany_CompanyUser::insertList($user_company_list);
		}

		return self::_addToken($user_id, $device_id, $expires_at);
	}

	/**
	 * @param int    $user_id
	 * @param string $device_id
	 * @param int    $expires_at
	 *
	 * @return string
	 * @throws \queryException
	 */
	protected static function _addToken(int $user_id, string $device_id, int $expires_at):string {

		$secret_key        = bin2hex(random_bytes(32));
		$bound_session_key = $device_id;

		$token_user = Gateway_Db_AnnouncementSecurity_TokenUser::insertOrUpdate($secret_key, $user_id, $bound_session_key, $expires_at);

		$payload = [
			"id"  => $user_id,
			"iat" => $token_user->created_at,
		];

		return Type_Jwt_Main::generate($secret_key, $payload);
	}
}
