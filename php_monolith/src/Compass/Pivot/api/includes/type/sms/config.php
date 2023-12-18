<?php

namespace Compass\Pivot;

/**
 * Класс для работы с конфиг-файлом api/conf/sms.php
 */
class Type_Sms_Config {

	/**
	 * Получить содерджимое конфиг-файла
	 *
	 */
	public static function get():array {

		// поскольку содержимое конфиг-файла не может поменяться нагорячую
		// то ничего не мешает положить его в глобальную переменную
		if (isset($GLOBALS[self::class])) {
			return $GLOBALS[self::class];
		}

		$GLOBALS[self::class] = getConfig("SMS_PROVIDER_LIST");
		return $GLOBALS[self::class];
	}

	/**
	 * Получаем ассоциативный массив с соотношением $phone_code => [$provider_id, $provider_id],
	 *
	 */
	public static function convertToAssocByPhoneCode():array {

		$output = [];

		// бежим по всем провайдерам
		$config = Type_Sms_Config::get();
		foreach ($config as $provider_id => $provider_info) {

			// бежим по всем обслуживаемым кодам телефона
			foreach ($provider_info["provide_phone_code_list"] as $phone_code) {
				$output[$phone_code] = array_merge($output[$phone_code] ?? [], [$provider_id]);
			}
		}

		return $output;
	}

	/**
	 * получаем массив кодов сотовых операторов, которые провайдер обслуживает с повышенным приоритетом
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getHighPriorityPhoneCodeList(string $provider_id):array {

		$config = Type_Sms_Config::get();
		if (!isset($config[$provider_id])) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("provider ({$provider_id}) not found");
		}

		return $config[$provider_id]["high_priority_phone_code_list"];
	}

	/**
	 * подменяем содержимое конфига
	 * работает только в тестах!
	 *
	 * @throws \parseException
	 */
	public static function substituteConfig(array $config):void {

		assertTestServer();

		// подменяем содержимое конфиг-файла, чтобы в будущем возвращать именно его
		$GLOBALS[self::class] = $config;
	}
}