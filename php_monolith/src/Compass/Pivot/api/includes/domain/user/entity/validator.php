<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для валидации данных вводимых пользователем
 */
class Domain_User_Entity_Validator {

	protected const _CONFIRM_CODE_LENGTH = 6; // длина кода подтверждения

	// массив доступных типов pivot_mbti
	protected const _AVAILABLE_MBTI_TYPE_LIST = [
		"INTJ", // стратег
		"INTP", // ученый
		"ENTJ", // командир
		"ENTP", // полемист
		"INFJ", // активист
		"INFP", // посредник
		"ENFJ", // тренер
		"ENFP", // борец
		"ISTJ", // администратор
		"ISFJ", // защитник
		"ESTJ", // менеджер
		"ESFJ", // консул
		"ISTP", // виртуоз
		"ISFP", // артист
		"ESTP", // делец
		"ESFP", // развлекатель
	];

	// массив доступных типов текста pivot_mbti
	protected const _AVAILABLE_MBTI_TEXT_TYPE_LIST = [
		"short_description",
		"description",
	];

	// массив доступных типов цветов
	protected const _AVAILABLE_MBTI_COLOR_LIST = [
		1, // зеленый
		2, // фиолетовый
	];

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	/**
	 * валидирует номер телефона
	 *
	 * @param string $phone_number
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 */
	public static function assertValidPhoneNumber(string $phone_number):void {

		// просто пытаемся объект телефонного номера создать. Если создался - телефон в порядке
		new \BaseFrame\System\PhoneNumber($phone_number);
	}

	/**
	 * Проверяем, назначен ли номер телефона текущему пользователю или другим
	 *
	 * @param int    $user_id
	 * @param string $phone_number
	 *
	 * @throws cs_PhoneAlreadyAssignedToCurrentUser
	 * @throws cs_PhoneAlreadyRegistered
	 */
	public static function assertPhoneIsNotUsedUserOrAnother(int $user_id, string $phone_number):void {

		try {

			$phone_number_user_id = Domain_User_Entity_Phone::getUserIdByPhone($phone_number);
			if ($phone_number_user_id !== $user_id) {
				throw new cs_PhoneAlreadyRegistered();
			} else {
				throw new cs_PhoneAlreadyAssignedToCurrentUser();
			}
		} catch (cs_PhoneNumberNotFound) {
			// если номер не прикреплен к пользователям, то всё ок
		}
	}

	/**
	 * выбрасывает исключение, если пользователь уже залогинен
	 *
	 * @param int $user_id
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
	 * @param int $user_id
	 *
	 * @throws cs_UserNotLoggedIn
	 */
	public static function assertLoggedIn(int $user_id):void {

		if ($user_id === 0) {
			throw new cs_UserNotLoggedIn();
		}
	}

	/**
	 * выбрасывает исключение, если пользователь забанен
	 *
	 * @param int $user_id
	 *
	 * @throws ParseFatalException
	 */
	public static function assertBanned(int $user_id):void {

		if ($user_id !== 0) {

			try {
				Domain_User_Entity_UserBanned::get($user_id);
			} catch (RowNotFoundException) {

				// все ок не забанен
				return;
			}
			throw new Domain_User_Exception_UserBanned();
		}
	}

	/**
	 * проверяем код подтверждения
	 *
	 * @param string $code
	 *
	 * @return bool
	 */
	public static function isConfirmCode(string $code):bool {

		// сравниваем на длину
		if (mb_strlen($code) != self::_CONFIRM_CODE_LENGTH) {
			return false;
		}

		return true;
	}

	/**
	 * выбрасывает исключение, если код невалидный
	 *
	 * @param string $code
	 *
	 * @throws cs_InvalidConfirmCode
	 */
	public static function assertValidConfirmCode(string $code):void {

		if (!self::isConfirmCode($code)) {
			throw new cs_InvalidConfirmCode();
		}
	}

	/**
	 * валидация имени профиля
	 *
	 * @param string $name
	 *
	 * @throws \cs_InvalidProfileName
	 */
	public static function assertValidProfileName(string $name):void {

		if (mb_strlen($name) === 0) {
			throw new \cs_InvalidProfileName();
		}
	}

	/**
	 * проверяем тип личности
	 *
	 * @param string $mbti_type
	 *
	 * @return bool
	 */
	public static function isMBTIType(string $mbti_type):bool {

		return in_array($mbti_type, self::_AVAILABLE_MBTI_TYPE_LIST);
	}

	/**
	 * проверяем тип текста типа личности
	 *
	 * @param string $text_type
	 *
	 * @return bool
	 */
	public static function isMBTITextType(string $text_type):bool {

		return in_array($text_type, self::_AVAILABLE_MBTI_TEXT_TYPE_LIST);
	}

	/**
	 * проверяем массив выделений пришедший от клиента
	 *
	 * @param array $color_selection_list
	 *
	 * @return bool
	 */
	public static function isMbtiColorSelectionList(array $color_selection_list):bool {

		foreach ($color_selection_list as $v) {

			if (!is_array($v) || count($v) != 4) {
				return false;
			}
			if (!isset($v["selection_id"]) || intval($v["selection_id"]) < 0) {
				return false;
			}
			if (!isset($v["position"]) || intval($v["position"]) < 0) {
				return false;
			}
			if (!isset($v["length"]) || intval($v["length"]) < 1) {
				return false;
			}
			if (!isset($v["color_id"]) || !in_array(intval($v["color_id"]), self::_AVAILABLE_MBTI_COLOR_LIST)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * валидация avatar
	 *
	 * @param string $avatar_file_map
	 *
	 * @throws cs_InvalidAvatarFileMap
	 */
	public static function assertValidAvatarFileMap(string $avatar_file_map):void {

		if (mb_strlen($avatar_file_map) === 0 || Type_Pack_File::getFileSource($avatar_file_map) != FILE_SOURCE_AVATAR) {
			throw new cs_InvalidAvatarFileMap();
		}
	}

	/**
	 * валидация идентификатора пользователя
	 *
	 * @throws Domain_User_Exception_IncorrectUserId
	 */
	public static function assertValidUserId(int $user_id):void {

		if ($user_id === 0) {
			throw new Domain_User_Exception_IncorrectUserId("incorrect user_id = 0");
		}
	}

	/**
	 * Проверяем что у пользователя не привязан номер телефона
	 *
	 * @throws Domain_User_Exception_Security_Phone_AlreadySet
	 */
	public static function assertUserHaveNotPhone(int $user_id):void {

		try {

			Domain_User_Entity_Phone::getPhoneByUserId($user_id);
		} catch (cs_UserPhoneSecurityNotFound|cs_PhoneNumberNotFound) {

			// номера нет
			return;
		}
		throw new Domain_User_Exception_Security_Phone_AlreadySet("phone is already set");
	}

	/**
	 * Проверяем что номер ни за кем не закреплен
	 *
	 * @throws Domain_User_Exception_Security_Phone_AlreadyTaken
	 */
	public static function assertPhoneIsNotTaken(string $phone_number):void {

		// проверяем что номер пользователя ни за кем не закреплен
		try {

			Domain_User_Entity_Phone::getUserIdByPhone($phone_number);
		} catch (cs_UserPhoneSecurityNotFound|cs_PhoneNumberNotFound) {

			// не занят
			return;
		}

		throw new Domain_User_Exception_Security_Phone_AlreadyTaken("phone is already taken");
	}
}
