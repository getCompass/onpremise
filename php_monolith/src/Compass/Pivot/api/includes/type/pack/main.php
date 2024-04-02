<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * класс для работы с незашифрованными map
 *
 * Class Type_Pack_Main
 */
class Type_Pack_Main {

	/**
	 * исключения для полей, вводимых пользователем
	 */
	protected const _SECURITY_TEST_EXCLUDE_LIST = [

	];

	/**
	 * заменить ключ на мапу
	 *
	 * @throws \cs_DecryptHasFailed
	 * @throws cs_UnknownKeyType
	 */
	public static function replaceKeyWithMap(string $key_name, string $key_value):string {

		switch ($key_name) {

			case "auth_key":
				return Type_Pack_Auth::doDecrypt($key_value);

			case "pivot_session_key":
				return Type_Pack_PivotSession::doDecrypt($key_value);

			case "file_key":
				return Type_Pack_File::doDecrypt($key_value);

			case "two_fa_key":
				return Type_Pack_TwoFa::doDecrypt($key_value);

			case "change_phone_story":
				return Type_Pack_ChangePhoneStory::doDecrypt($key_value);

			default:
				throw new cs_UnknownKeyType();
		}
	}

	/**
	 * заменяет map на key в любой структуре
	 *
	 */
	public static function replaceMapWithKeys(array $array):array {

		// заменяем все map в массиве на key
		$array = self::_replaceMaps($array);

		foreach ($array as &$v) {

			// преобразуем объект в массив, в конце делаем обратное преобразование
			$is_object = is_object($v);
			$v         = self::_convertObjectToArray($is_object, $v);

			if (!is_array($v)) {
				continue;
			}

			// рекурсивно вызываем ту же функцию на измененней массив
			$v = self::replaceMapWithKeys($v);

			// преобразуем массив обратно в объект
			$v = self::_convertArrayToObject($is_object, $v);
		}

		return $array;
	}

	/**
	 * заменяем все map в массиве на key
	 *
	 */
	protected static function _replaceMaps(array $array):array {

		// проверяем каждую сущность по названию
		$array = self::_replaceAuthMap($array);
		$array = self::_replacePivotSessionMap($array);
		$array = self::_replaceFileMap($array);
		$array = self::_replaceTwoFaMap($array);
		$array = self::_replaceChangePhoneStoryMap($array);

		return $array;
	}

	/**
	 * заменяем auth_map на auth_key
	 *
	 */
	protected static function _replaceAuthMap(array $array):array {

		// если пришел auth_map - заменяем
		if (isset($array["auth_map"])) {

			$array["auth_key"] = Type_Pack_Auth::doEncrypt($array["auth_map"]);
			unset($array["auth_map"]);
		}

		return $array;
	}

	/**
	 * заменяем file_map на file_key
	 *
	 */
	protected static function _replaceFileMap(array $array):array {

		// если пришел file_map - заменяем
		if (isset($array["file_map"])) {

			$array["file_key"] = mb_strlen($array["file_map"]) > 0 ? Type_Pack_File::doEncrypt($array["file_map"]) : "";
			unset($array["file_map"]);
		}

		return $array;
	}

	/**
	 * заменяем pivot_session_map на pivot_session_key
	 *
	 */
	protected static function _replacePivotSessionMap(array $array):array {

		// если пришел pivot_session_map - заменяем
		if (isset($array["pivot_session_map"])) {

			$array["pivot_session_key"] = Type_Pack_PivotSession::doEncrypt($array["pivot_session_map"]);
			unset($array["pivot_session_map"]);
		}

		return $array;
	}

	/**
	 * заменяем two_fa_map на two_fa_key
	 *
	 */
	protected static function _replaceTwoFaMap(array $array):array {

		// если пришел two_fa_map - заменяем
		if (isset($array["two_fa_map"])) {

			$array["two_fa_key"] = Type_Pack_TwoFa::doEncrypt($array["two_fa_map"]);
			unset($array["two_fa_map"]);
		}

		return $array;
	}

	/**
	 * заменяем change_phone_story_map на change_phone_story_key
	 *
	 */
	protected static function _replaceChangePhoneStoryMap(array $array):array {

		// если пришел two_fa_map - заменяем
		if (isset($array["change_phone_story_map"])) {

			$array["change_phone_story_key"] = Type_Pack_ChangePhoneStory::doEncrypt($array["change_phone_story_map"]);
			unset($array["change_phone_story_map"]);
		}

		return $array;
	}

	/**
	 * преобразуем объект в массив
	 *
	 * @param      $object
	 *
	 * @return array|object
	 */
	protected static function _convertObjectToArray(bool $is_object, $object) {

		if ($is_object) {
			return (array) $object;
		}

		return $object;
	}

	/**
	 * преобразуем массив в объект
	 *
	 * @param      $array
	 *
	 * @return array|object
	 */
	protected static function _convertArrayToObject(bool $is_object, $array) {

		if ($is_object) {
			return (object) $array;
		}

		return $array;
	}

	/**
	 * проверяет, что в структуре не осталось незашифрованных map
	 *
	 * @throws \parseException
	 */
	public static function doSecurityTest(array $array):array {

		// проходимся по каждому элементу массива
		foreach ($array as $k => $v) {

			// если это массив|объект, рекурсивно применяем к нему туже функцию
			if (is_array($v) || is_object($v)) {

				self::doSecurityTest((array) $v);
				continue;
			}

			// если null, то ничего неделаем
			if (is_null($v)) {
				continue;
			}

			// выбрасываем ошибку если пришел незакодированный map
			self::_throwIfArrayElementIsJson($k, $v);
		}

		return $array;
	}

	/**
	 * выбрасываем ошибку, если пришел json
	 *
	 * @throws \parseException
	 */
	protected static function _throwIfArrayElementIsJson(string $key, string $value):void {

		// если это поле исключение - пропускаем
		if (in_array($key, self::_SECURITY_TEST_EXCLUDE_LIST, true)) {
			return;
		}

		// если первый символ строки не { — это не json
		if (substr($value, 0, 1) != "{") {
			return;
		}

		// пробуем выполнить fromJson и смотрим что вернулось
		$json = fromJson($value);
		if (count($json) > 0) {
			throw new ParseFatalException("Key security test was failed!");
		}
	}

	// проверяем key на корректность
	public static function checkCorrectKey(string $key):string {

		// если передали некорректное значение для base64
		// функция base64_decode() вернет FALSE в случае, если входные данные содержат символы, не входящие в алфавит base64
		if (!base64_decode($key, true)) {
			throw new ParamException("passed incorrect value contains character from outside alphabet");
		}

		return $key;
	}

	// пробуем разбить key на server_type и сам key
	public static function tryExplodeKey(string $key):array {

		// отрезаем server_type
		$tt = explode(".", $key);

		// если получилось не 2 элемента
		if (count($tt) != 2) {
			throw new ParamException("passed malformed key in request");
		}

		// если передали некорректное значение для base64
		// функция base64_decode() вернет FALSE в случае, если входные данные содержат символы, не входящие в алфавит base64
		if (!base64_decode($tt[1], true)) {
			throw new ParamException("passed incorrect value contains character from outside alphabet");
		}

		return $tt;
	}
}
