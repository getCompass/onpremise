<?php

namespace Compass\Company;

/**
 * Класс для очистки данных
 */
class Domain_Member_Entity_Sanitizer {

	protected const _MAX_DESCRIPTION_LENGTH   = 40;
	protected const _MAX_STATUS_LENGTH        = 500;
	protected const _MAX_BADGE_CONTENT_LENGTH = 8;
	protected const _MBTI_TYPE_PER_QUERY      = 100;  // максимальное количество результатов в getListByMBTI

	/**
	 * Очистка short_description пользователя
	 */
	public static function sanitizeDescription(string $description):string {

		// удаляем лишние символы
		$description = trim(preg_replace([
			\BaseFrame\System\Character::EMOJI_REGEX,
			\BaseFrame\System\Character::COMMON_FORBIDDEN_CHARACTER_REGEX,
			\BaseFrame\System\Character::FANCY_TEXT_REGEX,
			\BaseFrame\System\Character::DOUBLE_SPACE_REGEX,
			\BaseFrame\System\Character::NEWLINE_REGEX,
		], ["", "", "", " ", ""], $description));

		// обрезаем
		return mb_substr($description, 0, self::_MAX_DESCRIPTION_LENGTH);
	}

	/**
	 * Очистка status пользователя
	 */
	public static function sanitizeStatus(string $status):string {

		// меняем emoji
		$status = Type_Api_Filter::replaceEmojiWithShortName($status);

		// удаляем лишнее
		$status = trim(preg_replace([
			"/([\r\n\f\v]){3,}/",
			\BaseFrame\System\Character::COMMON_FORBIDDEN_CHARACTER_REGEX,
		], ["\n\n", ""], $status));

		// обрезаем
		return mb_substr($status, 0, self::_MAX_STATUS_LENGTH);
	}

	/**
	 * Очищаем content бейджа пользователя
	 */
	public static function sanitizeBadgeContent(string $content):string {

		// удаляем лишнее
		$content = preg_replace([
			\BaseFrame\System\Character::EMOJI_REGEX,
			\BaseFrame\System\Character::COMMON_FORBIDDEN_CHARACTER_REGEX,
			\BaseFrame\System\Character::FANCY_TEXT_REGEX,
			"/\s*\R\s*/u",
		], ["", "", "", " "], $content);

		// обрезаем
		return mb_substr(trim($content), 0, self::_MAX_BADGE_CONTENT_LENGTH);
	}

	/**
	 * форматируем время
	 */
	public static function sanitizeJoinTime(int $time):string {

		return dayStartOnGreenwich($time);
	}

	/**
	 * метод для формирования массива пользователя
	 */
	public static function sanitizeUserList(array $batch_user_list, array $need_user_id_list):array {

		// сливаем всех пользователей в единую строку
		$implode_user_id_list = [];
		foreach ($batch_user_list as $v) {
			$implode_user_id_list = array_merge($implode_user_id_list, $v["user_list"]);
		}

		if (count($need_user_id_list) > 0) {
			return array_intersect($need_user_id_list, $implode_user_id_list);
		}
		return $implode_user_id_list;
	}

	/**
	 * форматируем оффсет для mbti
	 */
	public static function sanitizeGetMBTIListOffset(int $offset):int {

		if ($offset < 0) {
			return 0;
		}
		return $offset;
	}

	/**
	 * форматируем count для mbti
	 */
	public static function sanitizeGetMBTIListCount(int $count):int {

		if ($count < 1) {
			$count = self::_MBTI_TYPE_PER_QUERY;
		}
		return limit($count, 0, self::_MBTI_TYPE_PER_QUERY);
	}
}