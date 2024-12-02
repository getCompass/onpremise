<?php

namespace Compass\Company;

/**
 * Класс для валидации данных вводимых пользователем
 */
class Domain_Member_Entity_Validator {

	// время, в течении которого валидна подпись для action users
	protected const _USERS_SIGNATURE_EXPIRE = 60 * 2;

	// массив доступных типов
	protected const _AVAILABLE_MBTI_TYPE_LIST   = [
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

	/**
	 * Массив доступных цветов badge
	 *
	 * 1,2,3,6,7,8 - пользовательские
	 * 4,5 - боты
	 */
	protected const _AVAILABLE_BADGE_COLOR_LIST = [1, 2, 3, 4, 5, 6, 7, 8];

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	/**
	 * проверяем тип личности
	 *
	 * @throws cs_InvalidProfileMbti
	 */
	public static function assertMBTIType(string $mbti_type):void {

		if (!in_array($mbti_type, self::_AVAILABLE_MBTI_TYPE_LIST)) {
			throw new cs_InvalidProfileMbti();
		}
	}

	/**
	 * валидация бейджа
	 *
	 * @throws cs_InvalidProfileBadge
	 */
	public static function assertBadge(int $color_id, string $content):void {

		if (!in_array($color_id, self::_AVAILABLE_BADGE_COLOR_LIST)) {
			throw new cs_InvalidProfileBadge();
		}

		if (mb_strlen($content) < 1) {
			throw new cs_InvalidProfileBadge();
		}
	}

	/**
	 * проверяем время
	 */
	public static function assertJoinTime(int $join_time):void {

		if ($join_time < 0 || $join_time > time()) {
			throw new cs_InvalidProfileJoinTime();
		}
	}

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
	 */
	public static function assertBatchUserList(array $batch_user_list):void {

		foreach ($batch_user_list as $user_list) {

			if (!isset($user_list["user_list"])) {
				throw new cs_WrongSignature();
			}
		}
	}

	/**
	 * Выбрасываем исключение, если невалидный тип уведомления
	 */
	public static function assertValidMenuType(int $type):void {

		if (!in_array($type, Domain_Member_Entity_Menu::AVAILABLE_NOTIFICATION_TYPE_LIST)) {
			throw new Domain_Member_Exception_IncorrectMenuType("not valid notification type");
		}
	}

	/**
	 * Валидация бейджа
	 *
	 * @throws cs_InvalidProfileBadge
	 */
	public static function assertBadgeColor(int $badge_color_id):void {

		// передаем 0, когда хотим очистить
		if (!in_array($badge_color_id, array_merge(self::_AVAILABLE_BADGE_COLOR_LIST, [0]))) {
			throw new cs_InvalidProfileBadge();
		}
	}
}
