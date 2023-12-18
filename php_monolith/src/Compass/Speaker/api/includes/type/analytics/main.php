<?php

namespace Compass\Speaker;

/**
 * класс для работы с аналитикой звонка
 */
class Type_Analytics_Main {

	// список приватных-конфиденциальных полей, которые не должны попадать в аналитике в конечном счете
	protected const _PRIVATE_FIELD_LIST = ["feed_id", "sdps", "fingerprint", "remote-fingerprint", "remote-fingerprint-hash"];

	// функция скрывает приватные поля из массива рекурсивно
	public static function doHidePrivateFields(array $data):array {

		// если это не массив, то не трогаем
		if (!is_array($data)) {
			return $data;
		}

		// пробегаемся по всем полям
		foreach ($data as $k => $v) {

			// если такое поле запрещено
			if (in_array($k, self::_PRIVATE_FIELD_LIST, true)) {

				unset($data[$k]);
				continue;
			}

			// если поле — массив
			if (is_array($data[$k])) {
				$data[$k] = self::doHidePrivateFields($v);
			}
		}

		return $data;
	}
}
