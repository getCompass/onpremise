<?php

namespace Compass\FileNode;

/**
 * основной класс для работы с голосовыми
 *
 * структура для поля extra:
 * {
 *    "version": 1,
 *    "original_part_path": "/files/dd/ee/audio.aac",
 *    "duration": 5,
 *    "duration_ms": 5612,
 *    "waveform": [2,2,3,4,1,2,3,2,3,4],
 *    "listen_by": []
 * }
 */
class Type_File_Voice_Extra {

	// текущяя версия структуры
	protected const _EXTRA_VERSION = 1;

	// схема users
	protected const _EXTRA_SCHEMA = [
		1 => [
			"original_part_path" => "",
			"duration"           => 0,
			"duration_ms"        => 0,
			"waveform"           => [],
			"listen_by"          => [],
			"company_id"         => 0,
		],
	];

	// прослушан ли файл пользователем
	public static function isListenByUser(array $extra, int $user_id):int {

		$extra = self::_getExtra($extra);

		// если пользователя нет в массиве то отдаем 0
		if (!in_array($user_id, $extra["listen_by"])) {
			return 0;
		}

		return 1;
	}

	// формирует структуру extra
	public static function getExtra(string $original_part_path, int $duration_ms, array $waveform, int $user_id, int $company_id, string $company_url):array {

		// инициализируем extra
		$extra = self::_EXTRA_SCHEMA[self::_EXTRA_VERSION];

		// обновляем основные данные
		$extra["original_part_path"] = $original_part_path;
		$extra["duration"]           = floor($duration_ms / 1000);
		$extra["duration_ms"]        = $duration_ms;
		$extra["waveform"]           = $waveform;
		$extra["listen_by"][]        = $user_id;
		$extra["company_id"]         = $company_id;
		$extra["company_url"]         = $company_url;

		// устанавливаем текущую версию
		$extra["version"] = self::_EXTRA_VERSION;

		// отдаем ответ
		return $extra;
	}

	// получаем длительность голосухи в миллисекундах
	public static function getDurationInMs(array $extra):int {

		$extra = self::_getExtra($extra);

		// если у голосовые сообщения отсутствует длительность в миллисекундах, то берем его из секунд
		return $extra["duration_ms"] == 0 ? $extra["duration"] * 1000 : $extra["duration_ms"];
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получить актуальную структуру для extra
	protected static function _getExtra(array $extra):array {

		// если версия не совпадает - дополняем её до текущей
		if ($extra["version"] != self::_EXTRA_VERSION) {

			$extra            = array_merge(self::_EXTRA_SCHEMA[self::_EXTRA_VERSION], $extra);
			$extra["version"] = self::_EXTRA_VERSION;
		}

		return $extra;
	}
}