<?php

namespace Compass\Userbot;

/**
 * подготавливаем данные для ответа
 */
class Domain_Request_Action_PrepareResultData {

	/**
	 * выполняем
	 */
	public static function do(array $result_data):array {

		// заменяем поля в ответе, если необходимо
		$array = self::_replaceFields($result_data);

		foreach ($array as &$v) {

			// преобразуем объект в массив, в конце делаем обратное преобразование
			$is_object = is_object($v);
			$v         = $is_object ? (array) $v : $v;

			if (!is_array($v)) {
				continue;
			}

			// рекурсивно вызываем ту же функцию на изменённый массив
			$v = self::do($v);

			// преобразуем массив обратно в объект
			$v = $is_object ? (object) $v : $v;
		}

		return $array;
	}

	/**
	 * заменяем поле
	 */
	protected static function _replaceFields(array $array):array {

		// если пришел user_id
		if (isset($array["user_id"])) {
			$array["user_id"] = (string) "User-" . $array["user_id"];
		}

		return $array;
	}
}