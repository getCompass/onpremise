<?php

namespace Compass\FileNode;

/**
 * основной класс для работы с видео
 */
class Type_File_Video_Main extends Type_File_Main {

	// список доступных mime type
	public const ALLOWED_MIME_TYPE_LIST = [

		// extension не умеет в эти форматы
		// "video/webm",
		// "video/mpeg",
		// "video/x-flv",

		"application/octet-stream",
		"video/quicktime",
		"video/ogg",
		"video/mp4",
		"video/x-m4v",
		"video/x-ms-wmv",
		"video/x-ms-asf",
		"video/3gpp",
		"video/3gpp2",
	];

	// массив с размерами видео
	public const VIDEO_FORMAT_LIST = [
		2160,
		1080,
		720,
		480,
		360,
	];

	// список расширений
	public const EXTENSION_LIST = [
		"webm",
		"mpeg",
		"mp4",
		"wmv",
		"flv",
		"m4v",
		"3gp",
		"mkv",
		"avi",
		"mov",
		"vob",
	];

	// массив с доступными кодеками видео
	protected const _AVAILABLE_CODEC_LIST = [
		"h264",
		"hevc",
		// "h265",
		// "mpeg4",
	];

	// доступен ли кодек
	public static function isCodecAvailable(string $codec_name):bool {

		return in_array($codec_name, self::_AVAILABLE_CODEC_LIST);
	}

	// возвращает массив оступных видео кодеков
	public static function getAvailableCodecList():array {

		return self::_AVAILABLE_CODEC_LIST;
	}

	// получаем ключ для массива video_version_list {main_side}_{video_type}
	public static function getVideoItemKey(int $main_side, int $video_type):string {

		return "{$main_side}_{$video_type}";
	}

	/**
	 * Обновляем extra для видео файла
	 * Нам нет разницы на сколько блочить эт запись так как обращение к ней только от крона
	 * Но есть шанс того что другой крон тоже решит нарезать видео и в итоге только один из них обновит экстру
	 *
	 * @param string $file_key
	 * @param int    $video_type
	 * @param int    $format
	 * @param int    $size_kb
	 * @param bool   $is_success
	 *
	 * @return void
	 * @throws \returnException
	 */
	public static function updateVideoItem(string $file_key, int $video_type, int $format, int $size_kb, bool $is_success):void {

		Gateway_Db_FileNode_Main::beginTransaction();

		// берем запись с информацией о файле
		$file_row = Gateway_Db_FileNode_File::getForUpdate($file_key);

		// $size_kb будет здесь всегда, т/к условие точно такое же
		$video_key_item    = self::getVideoItemKey($format, $video_type);
		$file_row["extra"] = Type_File_Video_Extra::setVideoVersionItemStatus($file_row["extra"], $video_key_item, $size_kb, $is_success);

		try {
			self::_updateFileOnBalancer($file_key, $file_row["extra"]);
		} catch (\cs_SocketRequestIsFailed|Gateway_Socket_Exception_CompanyIsNotServed|Gateway_Socket_Exception_CompanyIsHibernated $e) {

			// для паблика отправляем сообщение
			if (!isTestServer() && !isStageServer()) {

				$text = "Не смогли обновить данные файла в file_balancer компании. Error: " . $e->getMessage();
				Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_EXCEPTION, $text);
			}

			// не ругаемся, чтобы не стопить выполнение очередей нарезки
			Type_System_Admin::log("updateVideoItemOnFileOnBalancer", [
				"file_key" => $file_key,
				"extra"    => $file_row["extra"],
				"error"    => $e->getMessage(),
			]);
		}

		// обновляем поле extra для файла в таблице file_node.file
		Gateway_Db_FileNode_File::update($file_key, [
			"extra"      => $file_row["extra"],
			"updated_at" => time(),
		]);

		Gateway_Db_FileNode_Main::commit();
	}
}