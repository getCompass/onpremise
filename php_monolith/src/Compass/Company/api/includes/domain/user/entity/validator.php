<?php

namespace Compass\Company;

/**
 * Класс для валидации данных вводимых пользователем
 */
class Domain_User_Entity_Validator {

	public const MAX_SEARCH_QUERY_LENGTH = 80; // маскимальная длина поискового запроса

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	/**
	 * выбрасывает исключение, если прислан неверный токен для компании
	 *
	 * @throws cs_InvalidUserCompanySessionToken
	 */
	public static function assertValidUserCompanySessionToken(string $user_company_session_token):void {

		if (mb_strlen($user_company_session_token) < 1) {
			throw new cs_InvalidUserCompanySessionToken();
		}
	}

	/**
	 * выбрасывает исключение, если пользователь уже залогинен
	 *
	 * @throws cs_UserAlreadyLoggedIn
	 */
	public static function assertNotLoggedIn(int $user_id):void {

		if ($user_id !== 0) {
			throw new cs_UserAlreadyLoggedIn();
		}
	}

	/**
	 * выбрасывает исключение, если пользователь не залогинен
	 *
	 * @throws cs_UserNotLoggedIn
	 */
	public static function assertLoggedIn(int $user_id):void {

		if ($user_id === 0) {
			throw new cs_UserNotLoggedIn();
		}
	}

	/**
	 * проверка кол-ва приглашений
	 *
	 * @throws cs_InvitesCountLimit
	 */
	public static function assertValidCountOfInvites(array $invites):void {

		if (count($invites) > Type_Invite_Main::MAX_INVITE_COUNT) {
			throw new cs_InvitesCountLimit(Type_Invite_Main::MAX_INVITE_COUNT);
		}
	}

	/**
	 * проверка списка id пользователей
	 *
	 * @throws cs_IncorrectUserId
	 * @throws cs_UserIdListEmpty
	 */
	public static function assertValidUserIdList(array $user_id_list):void {

		if (count($user_id_list) < 1) {
			throw new cs_UserIdListEmpty("passed empty user_id_list");
		}

		foreach ($user_id_list as $item) {

			// если user_id нулевой
			if ($item < 1) {
				throw new cs_IncorrectUserId("passed invalid user_id");
			}
		}
	}

	/**
	 * проверка id пользователя
	 *
	 * @throws cs_IncorrectUserId
	 */
	public static function assertValidUserId(int $user_id):void {

		// если user_id нулевой
		if ($user_id < 1) {
			throw new cs_IncorrectUserId("passed invalid user_id");
		}
	}

	/**
	 * Проверить, что не является рут-пользователем.
	 *
	 */
	public static function assertNotRootUserId(int $user_id):void {

		$config = Type_Company_Config::init()->get(Domain_Company_Entity_Config::ROOT_USER_ID);

		// проверяем если не существует конфига, то создаём
		if (!isset($config["value"])) {

			// получаем user_id рут пользователя
			$root_user_id = Gateway_Socket_Pivot::getRootUserId();

			// добавляем в конфиг компании
			$time   = time();
			$value  = ["value" => $root_user_id];
			$config = new Struct_Db_CompanyData_CompanyConfig(Domain_Company_Entity_Config::ROOT_USER_ID, $time, $time, $value);
			Gateway_Db_CompanyData_CompanyConfig::insert($config);

			// конвертируем в массив чтобы ниже не падать
			$config = $config->convertToArray();
		}

		if ($user_id == $config["value"]) {
			throw new cs_ActionNotAvailable("action not available for root user");
		}
	}
}
