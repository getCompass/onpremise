<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Класс для работы с конфиг-файлом api/conf/smslimit.php
 */
class Type_Sms_limit {

	/**
	 * Получить содержимое конфиг-файла
	 */
	public static function get():array {

		// поскольку содержимое конфиг-файла не может поменяться нагорячую
		// то ничего не мешает положить его в глобальную переменную
		if (isset($GLOBALS[self::class])) {
			return $GLOBALS[self::class];
		}

		$GLOBALS[self::class] = getConfig("SMSLIMIT");
		return $GLOBALS[self::class];
	}

	/**
	 * Получаем лимит для этого номер кода
	 */
	public static function getSmsLimit(string $phone_code):int {

		$config = self::get();
		return $config[$phone_code] ?? 1;
	}

	/**
	 * подменяем содержимое конфига
	 * работает только в тестах!
	 *
	 * @throws \parseException
	 */
	public static function substituteConfig(array $config):void {

		ServerProvider::assertTest();

		// подменяем содержимое конфиг-файла, чтобы в будущем возвращать именно его
		$GLOBALS[self::class] = $config;
	}
}