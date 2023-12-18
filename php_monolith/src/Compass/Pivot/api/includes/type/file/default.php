<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Server\ServerProvider;

/**
 * дефолтный класс для работы c дефолтными файлами
 */
class Type_File_Default {

	protected const _PATH_TO_FILE = PATH_WWW . "default_file/";

	/**
	 * проверяем наличие актуально загруженного файла
	 *
	 * @throws \parseException
	 * @throws \returnException
	 * @mixed
	 */
	public static function uploadFile(string $dictionary_key, string $file_name, int $file_source, string $file_hash_config):void {

		// получаем хеш файла
		$file_path = self::_PATH_TO_FILE . $file_name;
		$file_hash = Type_File_Utils::getFileHash($file_path);

		// проверяем что хеш файла совпадает с хешом в конфиге
		if ($file_hash_config !== $file_hash) {
			throw new ParamException("file hash does not match {$dictionary_key}");
		}

		// сравниваем его с хешом файла в базе, если не совпадает, загружаем файл на ноду
		try {
			$file_row = Gateway_Db_PivotSystem_DefaultFileList::get($dictionary_key);
		} catch (\cs_RowIsEmpty) {

			self::_uploadFile($file_path, $file_source, $dictionary_key, $file_hash);
			return;
		}

		if ($file_hash !== $file_row->file_hash) {
			self::_uploadFile($file_path, $file_source, $dictionary_key, $file_hash);
		}
	}

	/**
	 * Загружаем файл
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _uploadFile(string $file_path, int $file_source, string $dictionary_key, string $file_hash):void {

		// загружаем файл
		$url      = Gateway_Socket_PivotFileBalancer::getNodeForUpload($file_source);
		$file_key = Gateway_Socket_FileNode::uploadDefaultFile($url, $file_path, $file_source);

		// обновляем сервесную таблицу
		Gateway_Db_PivotSystem_DefaultFileList::set($dictionary_key, $file_key, $file_hash);
	}

	/**
	 * проверяем наличие актуально загруженного файла
	 *
	 * @mixed
	 */
	public static function getDefaultFileList():array {

		// загружаем файлы из отдельного конфига
		if (ServerProvider::isCi() && file_exists(self::_PATH_TO_FILE . "test/manifest.json")) {
			return fromJson(file_get_contents(self::_PATH_TO_FILE . "test/manifest.json"));
		}

		return fromJson(file_get_contents(self::_PATH_TO_FILE . "manifest.json"));
	}

	/**
	 * проверяем наличие актуально загруженного файла
	 *
	 * @mixed
	 */
	public static function getDefaultFileListOld():array {

		return getConfig("DEFAULTFILE_LIST");
	}
}