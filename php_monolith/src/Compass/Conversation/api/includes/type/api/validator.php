<?php

namespace Compass\Conversation;

/**
 * класс для фильтрации одинаковых сущностей внутри модулей общий для всех
 */
class Type_Api_Validator {

	protected const _SMS_CODE_LENGTH       = 6;         // длина смс-кода
	protected const _MAX_GROUP_NAME_LENGTH = 40;        // максимальная длина имени группы

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	// проверяем код который пользователь получает либо по email либо по sms
	public static function isSmsCode(string $sms_code):bool {

		// сравниваем на длину
		if (mb_strlen($sms_code) != self::_SMS_CODE_LENGTH) {
			return false;
		}

		return true;
	}

	// валидирует поисковую строку для sphinx
	public static function isSearchQuery(string $query):bool {

		// если длина запроса равна нулю
		if (mb_strlen($query) < 1) {
			return false;
		}

		return true;
	}

	// проверяем валидность название группового диалога
	public static function isGroupName(string $group_name):bool {

		// название не пустое
		if (mb_strlen($group_name) < 1) {
			return false;
		}

		// длина названия не больше максимального
		if (mb_strlen($group_name) > self::_MAX_GROUP_NAME_LENGTH) {
			return false;
		}

		return true;
	}

	/**
	 * проверяем, есть ли в названии эмоджи
	 *
	 */
	public static function isStringContainEmoji(string $input_string):bool {

		// получаем список всех смайликов из конфига
		$emoji_list = \BaseFrame\Conf\Emoji::EMOJI_LIST;

		// обрабатываем все эмоджи
		$tmp = str_replace(array_keys($emoji_list), $emoji_list, $input_string);

		// если есть изменения
		if ($tmp !== $input_string) {
			return true;
		}

		// получаем список флагов эмоджи
		$emoji_list = \BaseFrame\Conf\Emoji::EMOJI_FLAG_LIST;

		// обрабатываем все флаги эмоджи
		$tmp = str_replace(array_keys($emoji_list), $emoji_list, $input_string);

		// если есть изменения
		if ($tmp !== $input_string) {
			return true;
		}

		return false;
	}

	/**
	 * проверяем user_id на корректность
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public static function isCorrectUserId(int $user_id):bool {

		// проверяем user_id
		if ($user_id < 1) {
			return false;
		}

		return true;
	}
}
