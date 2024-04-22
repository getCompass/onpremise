<?php

namespace Compass\Premise;

/**
 * Класс для валидации данных сущности пользователя
 */
class Domain_User_Entity_Validator {

	// время, в течении которого валидна подпись для action users
	protected const _USERS_SIGNATURE_EXPIRE = 60 * 2;

	/**
	 * Выбрасываем исключение, если список невалидный
	 *
	 * @throws cs_IncorrectUserId
	 */
	public static function assertNeedUserIdList(array $need_user_id_list):void {

		foreach ($need_user_id_list as $user_id) {

			if (!is_int($user_id)) {
				throw new cs_IncorrectUserId();
			}
		}
	}

	/**
	 * Выбрасываем исключение, если список невалидный
	 *
	 * @throws cs_WrongSignature
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function assertBatchUserList(array $batch_user_list):void {

		foreach ($batch_user_list as $user_list) {

			if (!isset($user_list["premise_user_list"]) || !isset($user_list["signature"])) {
				throw new cs_WrongSignature();
			}

			// проверяем подпись
			self::_verifyUsersSignature($user_list["premise_user_list"], $user_list["signature"]);
		}
	}

	/**
	 * Проверяем подпись
	 *
	 * @throws cs_WrongSignature
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _verifyUsersSignature(array $user_id_list, string $signature):void {

		// формируем список юзеров
		if (!self::verifyUserListSignature($user_id_list, $signature)) {
			throw new cs_WrongSignature();
		}
	}

	/**
	 * Проверить подпись для пользователей компании
	 *
	 * @param array  $member_id_list
	 * @param string $signature
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function verifyUserListSignature(array $member_id_list, string $signature):bool {

		$temp = explode("_", $signature);

		// проверяем, корректная ли пришла подпись
		if (count($temp) != 2) {
			return false;
		}

		// проверяем время
		$time = intval($temp[1]);
		if (time() > $time + self::_USERS_SIGNATURE_EXPIRE) {
			return false;
		}

		// сверяем подпись
		if ($signature != \CompassApp\Controller\ApiAction::getUsersSignature($member_id_list, $time)) {
			return false;
		}

		return true;
	}
}
