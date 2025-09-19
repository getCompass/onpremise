<?php

namespace Compass\FileNode;

/**
 * Главный класс с общими для всех файлов функциями
 * Класс работает с файлами независимо от их типа
 *
 * ---
 * part_path - относительный путь до файла от папки /www/files/
 * пример: "12/12/32/22/44/test.txt"
 * ---
 * file_path - полный путь до файла в файловой системе (путь до папки с файлами + part_path)
 * пример: "/home/dev/php_file_node/www/files/12/12/32/22/44/test.txt"
 * ---
 * url - ссылка с подставленным доменом (domain + part_path)
 * пример: "https://file1.example.ru/files/12/12/32/22/44/test.txt"
 * ---
 */
class Type_File_Main
{
	// статус конвертации файла
	public const CONVERT_STATUS_OK         = 1;
	public const CONVERT_STATUS_PROCESSING = 2;
	public const CONVERT_STATUS_ERROR      = 3;

	// таблица соответствия mime_type с file_type и расширений
	protected const _FILE_TYPE_REL = [

		FILE_TYPE_IMAGE    => Type_File_Image_Main::ALLOWED_MIME_TYPE_LIST,
		FILE_TYPE_VIDEO    => Type_File_Video_Main::ALLOWED_MIME_TYPE_LIST,
		FILE_TYPE_AUDIO    => Type_File_Audio_Main::ALLOWED_MIME_TYPE_LIST,
		FILE_TYPE_VOICE    => Type_File_Voice_Main::ALLOWED_MIME_TYPE_LIST,
		FILE_TYPE_DOCUMENT => Type_File_Document_Main::ALLOWED_MIME_TYPE_LIST,
		FILE_TYPE_ARCHIVE  => Type_File_Archive_Main::ALLOWED_MIME_TYPE_LIST,
	];

	// массив соответствия mime_type с extension
	protected const _TYPE_EXTENSION_REL = [

		FILE_TYPE_IMAGE    => Type_File_Image_Main::EXTENSION_LIST,
		FILE_TYPE_VIDEO    => Type_File_Video_Main::EXTENSION_LIST,
		FILE_TYPE_AUDIO    => Type_File_Audio_Main::EXTENSION_LIST,
		FILE_TYPE_VOICE    => Type_File_Voice_Main::EXTENSION_LIST,
		FILE_TYPE_DOCUMENT => Type_File_Document_Main::EXTENSION_LIST,
		FILE_TYPE_ARCHIVE  => Type_File_Archive_Main::EXTENSION_LIST,
	];

	// строковые названия файлов
	public const FILE_TYPE_NAME = [
		FILE_TYPE_DEFAULT  => "file",
		FILE_TYPE_IMAGE    => "image",
		FILE_TYPE_VIDEO    => "video",
		FILE_TYPE_AUDIO    => "audio",
		FILE_TYPE_DOCUMENT => "document",
		FILE_TYPE_ARCHIVE  => "archive",
		FILE_TYPE_VOICE    => "voice",
	];

	// статус загрузки файла
	public const FILE_STATUS_PROCESSING = 0;
	public const FILE_STATUS_APPROVED   = 1;
	public const FILE_STATUS_RESTRICTED = 2;
	public const FILE_STATUS_DELETED    = 3;

	// названия статусов для клиентов
	public const FILE_STATUS_NAME = [
		self::FILE_STATUS_PROCESSING => "processing",
		self::FILE_STATUS_APPROVED   => "approved",
		self::FILE_STATUS_RESTRICTED => "restricted",
		self::FILE_STATUS_DELETED    => "deleted",
	];

	// получает тип файла по его mime_type
	public static function getFileType(string $file_path, string $mime_type, int $size_kb, int $file_source, string $extension): int
	{

		// по умолчанию - файл
		$file_type = FILE_TYPE_DEFAULT;

		// бежим по всем типам файлов, пытаемся найти нужный mime_type
		foreach (self::_FILE_TYPE_REL as $k => $v) {

			// если нашли - ставим его и обрываем цикл
			if (in_array($mime_type, $v)) {

				$file_type = $k;

				// если mime-type совпдает с другим типом, проверим расширение
				if (!isset(self::_TYPE_EXTENSION_REL[$file_type]) || !in_array($extension, self::_TYPE_EXTENSION_REL[$file_type])) {
					continue;
				}
				break;
			}
		}

		// если нет доступного расширения - возвращаем тип файл
		if (!isset(self::_TYPE_EXTENSION_REL[$file_type]) || !in_array($extension, self::_TYPE_EXTENSION_REL[$file_type])) {
			return FILE_TYPE_DEFAULT;
		}

		// проверяем дополнительные условия для получения типа файла
		return self::_checkAdditionalConditionForGetFileType($file_type, $size_kb, $file_source, $file_path);
	}

	// проверяем дополнительные условия для получения типа файла
	protected static function _checkAdditionalConditionForGetFileType(int $file_type, int $size_kb, int $file_source, string $file_path): string
	{

		// если это картинка и размер больше 30 mb - загружаем её как файл
		if ($file_type == FILE_TYPE_IMAGE) {

			if ($size_kb > IMAGE_MAX_SIZE_KB) {
				return FILE_TYPE_DEFAULT;
			}

			// здесь ловим варнинг так как если к нам прилетела плохая картинка то она скорее всего с чем то вредноносным
			try {

				[$width, $height] = getimagesize($file_path);
			} catch (WarningException) {
				return FILE_TYPE_DEFAULT;
			}
			if ($width > IMAGE_MAX_SIDE_PX || $height > IMAGE_MAX_SIDE_PX) {
				return FILE_TYPE_DEFAULT;
			}
		}

		// если голосовое, и тип не прикрепления не совпадает то это аудио
		if ($file_type == FILE_TYPE_VOICE && $file_source != FILE_SOURCE_MESSAGE_VOICE) {
			$file_type = FILE_TYPE_AUDIO;
		}

		// если это видео и в нем нет потоков - отдаем файл
		if ($file_type == FILE_TYPE_VIDEO) {

			$video_info = Type_Extension_File::getVideoInfo($file_path);

			// если кодек не доступен - отдаем как файл
			if (!Type_File_Video_Main::isCodecAvailable($video_info["codec_name"])) {
				return FILE_TYPE_DEFAULT;
			}
		}

		if ($file_type == FILE_TYPE_AUDIO) {

			$video_info = Type_Extension_File::getVideoInfo($file_path);

			// проверяем что дейтсвительно пришло аудио - duration может быть пустым в битых файлах, и не пустым если это видео с дорожкой
			if ($video_info["height"] > 0 && isset($video_info["codec_name"]) && Type_File_Video_Main::isCodecAvailable($video_info["codec_name"])) {
				$file_type = FILE_TYPE_VIDEO;
			}
		}

		return $file_type;
	}

	// проверяем, что пришел корректный file_source
	public static function tryGetFileSource(int $file_type, int $file_source): int
	{

		// если источник превью и это не картинка то отдаем exception
		if ($file_type == FILE_TYPE_DEFAULT && $file_source == FILE_SOURCE_MESSAGE_PREVIEW_IMAGE) {
			throw new cs_InvalidFileTypeForSource();
		}

		return $file_source;
	}

	// создаем папку под файл
	public static function moveFileToRandomDir(string $src_file_path, string $file_extension, int $company_id): string
	{

		$part_path = Type_File_Utils::generatePathPart($file_extension, $company_id);

		// создаем папку с нужным названием
		$file_path = Type_File_Utils::getFilePathFromPartPath($part_path);
		Type_File_Utils::makeDir($file_path);

		// делаем проверку, что такой файл уже существует
		self::_throwIfFileExist($file_path);

		// перемещаем файл
		Type_File_Utils::moveFromTmpToFiles($src_file_path, $file_path);

		return $part_path;
	}

	// создаем папку под файл
	public static function moveFileToRandomDirByMigration(string $src_file_path, string $file_extension, int $company_id): string
	{

		$part_path = Type_File_Utils::generatePathPart($file_extension, $company_id);

		// создаем папку с нужным названием
		$file_path = Type_File_Utils::getFilePathFromPartPath($part_path);
		Type_File_Utils::makeDir($file_path);

		// делаем проверку, что такой файл уже существует
		self::_throwIfFileExist($file_path);

		// перемещаем файл
		Type_File_Utils::moveFromFilesToFilesForMigration($src_file_path, $file_path);

		return $part_path;
	}

	// создаем папку под файл
	public static function generatePathPart(string $save_file_path, string $src_file_path, string $file_extension): string
	{

		$part_path = Type_File_Utils::generatePathPartByMigration($save_file_path, $file_extension);

		// создаем папку с нужным названием
		$file_path = Type_File_Utils::getFilePathFromPartPath($part_path);
		Type_File_Utils::makeDir($file_path);

		// делаем проверку, что такой файл уже существует
		self::_throwIfFileExist($file_path);

		// перемещает файл который был загружен не с помощью post
		if (copy($src_file_path, $file_path)) {
			unlink($src_file_path); // nosemgrep
		}

		// задаем права
		chmod($file_path, 0644);

		return $part_path;
	}

	// проверяет что такой файл есть
	protected static function _throwIfFileExist(string $file_path): void
	{

		// если существует выбрасываем ошибку
		if (file_exists($file_path)) {

			throw new \ParseException("File is already exist {$file_path} ");
		}
	}

	// возвращает массив соотношений mime_type
	public static function getMimeTypeToFileTypeRel(): array
	{

		return self::_FILE_TYPE_REL;
	}

	// возвращает массив соотношений extension
	public static function getExtensionToFileTypeRel(): array
	{

		return self::_TYPE_EXTENSION_REL;
	}

	// создает необходимые записи на file_node при добавлении файла
	public static function create(int $user_id, int $file_type, int $file_source, int $size_kb, string $mime_type, string $file_name, string $file_extension, array $extra, string $part_path, string $file_hash, int $status, bool $is_cdn = false): array
	{

		// делаем сначала сокет запрос для сохранения инфы о файле на балансере и получения map
		[$file_key, $download_token] = self::_saveOnBalancer($file_type, $file_source, $size_kb, $mime_type, $file_name, $file_extension, $extra, $file_hash, $status, $is_cdn, $user_id);

		$file_row = [
			"file_key"       => $file_key,
			"file_type"      => $file_type,
			"file_source"    => $file_source,
			"is_deleted"     => 0,
			"is_cdn"         => $is_cdn ? 1 : 0,
			"status"         => $status,
			"created_at"     => time(),
			"updated_at"     => 0,
			"size_kb"        => $size_kb,
			"access_count"   => 0,
			"last_access_at" => time(),
			"user_id"        => $user_id,
			"mime_type"      => $mime_type,
			"file_name"      => $file_name,
			"file_extension" => $file_extension,
			"part_path"      => $part_path,
			"extra"          => $extra,
			"file_hash"      => $file_hash,
		];
		Gateway_Db_FileNode_File::insert($file_row);

		return [$file_row, $download_token];
	}

	/**
	 * Сохраняем файл на балансере
	 */
	protected static function _saveOnBalancer(int $file_type, int $file_source, int $size_kb, string $mime_type, string $file_name, string $file_extension, array $extra, string $file_hash, int $status, bool $is_cdn = false, int $user_id = 0): array
	{

		// делаем сокет запрос для сохранения инфы о файле на balancer
		[$status, $response] = Gateway_Socket_FileBalancer::doCall("files.trySaveFile", $extra["company_id"], $extra["company_url"], [
			"file_type"      => $file_type,
			"file_source"    => $file_source,
			"is_cdn"         => $is_cdn ? 1 : 0,
			"status"         => $status,
			"node_id"        => NODE_ID,
			"size_kb"        => $size_kb,
			"mime_type"      => $mime_type,
			"file_name"      => $file_name,
			"file_extension" => $file_extension,
			"extra"          => $extra,
			"file_hash"      => $file_hash,
		], $user_id);

		// если не ок — бросаем экшепшен
		if ($status != "ok") {
			throw new \ReturnException("Unhandled error_code from socket call in " . __METHOD__);
		}

		// отдаем file_key
		return [$response["file_row"]["file_key"], $response["download_token"] ?? ""];
	}

	// обновляем данные файла
	public static function updateFile(string $file_key, array $extra, string $file_extension = ""): void
	{

		// обновляем поле extra для файла в таблице file_node.file
		Gateway_Db_FileNode_File::update($file_key, [
			"extra"      => $extra,
			"updated_at" => time(),
		]);

		self::_updateFileOnBalancer($file_key, $extra, $file_extension);
	}

	// сохраняем данные файла
	protected static function _updateFileOnBalancer(string $file_key, array $extra, string $file_extension = ""): void
	{

		// отправляем socket запрос для обновления информации о файле
		[$status,] = Gateway_Socket_FileBalancer::doCall("files.doUpdateFile", $extra["company_id"], $extra["company_url"], [
			"file_key"       => $file_key,
			"extra"          => $extra,
			"file_extension" => $file_extension,
		]);

		// если не ок — бросаем экшепшен
		if ($status != "ok") {
			throw new \ReturnException("Unhandled error_code from socket call in " . __METHOD__);
		}
	}

	/**
	 * Записываем содержимое документа на файловый балансировщик
	 */
	public static function setContent(string $file_key, array $extra, string $content): void
	{

		// отправляем socket запрос для обновления информации о файле
		[$status,] = Gateway_Socket_FileBalancer::doCall("files.setContent", $extra["company_id"], $extra["company_url"], [
			"file_key" => $file_key,
			"content"  => $content,
		]);

		// если не ок — бросаем экшепшен
		if ($status != "ok") {
			throw new \BaseFrame\Exception\Domain\ReturnFatalException("Unhandled error_code from socket call in " . __METHOD__);
		}
	}
}
