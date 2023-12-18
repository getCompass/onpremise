<?php

namespace Compass\Pivot;

// класс для валидации данных вводимых пользователем
class Type_Api_Validator {

	protected const _MAX_SEARCH_QUERY_LENGTH = 64;         // максимальная длина поискового запроса

	// массив доступных типов mbti
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

	// массив доступных типов текста mbti
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

	// проверяем токен на валидность
	public static function isNotificationToken(string $token):bool {

		if (mb_strlen($token) < 1) {
			return false;
		}
		return true;
	}

	// валидирует поисковую строку для sphinx
	public static function isSearchQuery(string $query):bool {

		// если длина запроса меньше 1 символа или больше максимальной длины
		if (mb_strlen($query) < 1 || mb_strlen($query) > self::_MAX_SEARCH_QUERY_LENGTH) {
			return false;
		}

		return true;
	}

	// проверяем тип mbti
	public static function isMBTIType(string $mbti_type):bool {

		return in_array($mbti_type, self::_AVAILABLE_MBTI_TYPE_LIST);
	}
}
