<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Server\ServerProvider;
use BaseFrame\System\File;

/**
 * дефолтный класс для работы c кастомными пользовательскими файлами
 */
class Type_File_Custom {

	protected const _PATH_TO_FILE = PATH_WWW . "custom_files/";

	/**
	 * проверяем наличие актуально загруженного файла
	 *
	 * @param string $dictionary_key
	 * @param string $file_name
	 * @param int    $file_source
	 * @param string $file_hash_config
	 *
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
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
	 * @param string $file_path
	 * @param int    $file_source
	 * @param string $dictionary_key
	 * @param string $file_hash
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
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
	 * получаем хэши и список файлов
	 *
	 * @mixed
	 */
	public static function getCustomFileList():array {

		$test_manifest_file = File::init(self::_PATH_TO_FILE . "test", "manifest.json");

		// загружаем файлы из отдельного конфига
		if (ServerProvider::isCi() && $test_manifest_file->isExists()) {
			return fromJson($test_manifest_file->read());
		}

		$manifest_file = File::init(self::_PATH_TO_FILE, "manifest.json");
		if (!$manifest_file->isExists()) {
			return [];
		}
		return fromJson($manifest_file->read());
	}
}