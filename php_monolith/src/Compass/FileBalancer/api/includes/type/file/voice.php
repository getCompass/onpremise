<?php

namespace Compass\FileBalancer;

/*
 * класс для работы с голосовыми сообщениями
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

class Type_File_Voice {

	// текущяя версия структуры
	protected const _EXTRA_VERSION = 3;

	// схема users
	protected const _EXTRA_SCHEMA = [
		1 => [
			"original_part_path" => "",
			"duration"           => 0,
			"waveform"           => [],

		],
		2 => [
			"original_part_path" => "",
			"duration"           => 0,
			"waveform"           => [],
			"listen_by"          => [],
		],
		3 => [
			"original_part_path" => "",
			"duration"           => 0,
			"duration_ms"        => 0,
			"waveform"           => [],
			"listen_by"          => [],
		],
	];

	// метод для того, чтобы прослушать голосовое сообщение
	public static function doListenVoiceFile(string $file_map, int $user_id):void {

		// открываем транзкцию
		Type_Db_File::beginTransaction($file_map);

		// получаем информацию о файле
		$file_row = Type_Db_File::getForUpdate($file_map);

		// если аудио-файл прослушан пользователем
		if (Type_File_Voice::isListenByUser($file_row["extra"], $user_id)) {

			Type_Db_File::rollback($file_map);
			return;
		}

		// помечаем аудио-файл прослушанным
		$extra = Type_File_Voice::addToListenBy($file_row["extra"], $user_id);
		self::_updateExtra($file_map, $extra);

		// выполняем транзакцию
		if (!Type_Db_File::commit($file_map)) {

			Type_Db_File::rollback($file_map);
			throw new returnException(__CLASS__ . ": transaction commit failed");
		}
	}

	// обновляем экстру файла
	protected static function _updateExtra(string $file_map, array $extra):void {

		// обновляем запись с файлом
		Type_Db_File::set($file_map, [
			"extra"      => $extra,
			"updated_at" => time(),
		]);
	}

	// доступно ли сообщение для прослушивания
	public static function isAllowToListen(string $file_map):bool {

		if (Type_Pack_File::getFileType($file_map) != FILE_TYPE_VOICE) {
			return false;
		}

		return true;
	}

	// прослушан ли файл пользователем
	public static function isListenByUser(array $extra, int $user_id):bool {

		$extra = self::_getExtra($extra);

		// если пользователя нет в массиве то отдаем false
		if (!in_array($user_id, $extra["listen_by"])) {
			return false;
		}

		return true;
	}

	// помечает файл прослушанным
	public static function addToListenBy(array $extra, int $user_id):array {

		$extra = self::_getExtra($extra);

		// если пользователь уже прослушал
		if (self::isListenByUser($extra, $user_id)) {
			return $extra;
		}

		// добавляем нашего пользователя
		$extra["listen_by"][] = $user_id;

		return $extra;
	}

	// получаем длительность голосухи в миллисекундах
	public static function getDurationInMs(array $extra):int {

		$extra = self::_getExtra($extra);

		// если у голосовухи отсутствует длительность в миллисекундах, то берем его из секунд
		return $extra["duration_ms"] == 0 ? $extra["duration"] * 1000 : $extra["duration_ms"];
	}

	// -------------------------------------------------------
	// PROTECTED
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