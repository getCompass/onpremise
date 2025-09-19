<?php

namespace Compass\FileBalancer;

use BaseFrame\Exception\Request\ParamException;

/**
 * группа методов для загрузки файлов
 */
class ApiV2_Files extends \BaseFrame\Controller\Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"getBatching",
	];

	protected const _MAX_FILES_COUNT = 150;     // максимальное количество файлов в запросе

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Получить файлы по ключам
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public function getBatching():array {

		$file_key_list = $this->post(\Formatter::TYPE_ARRAY, "file_key_list");

		// оставляем только уникальные значения в массиве
		$file_key_list = array_unique($file_key_list);

		$this->_throwIfFileListIsIncorrect($file_key_list);
		$file_map_list = $this->_doDecryptFileKeyList($file_key_list);

		// получаем информацию о файлах
		$file_list = Type_File_Main::getAll($file_map_list);

		// приводим массив не удаленных файлов к формату
		$output = $this->_formatFileList($file_list);

		return $this->ok([
			"file_list" => (array) $output,
		]);
	}

	/**
	 * Форматируем список файлов
	 *
	 * @param array $file_list
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	protected function _formatFileList(array $file_list):array {

		$output = [];
		foreach ($file_list as $item) {

			// получаем ссылку на ноду, где расположен файл
			$node_url = Type_Node_Config::getNodeUrl($item["node_id"]);

			// приводим файл к формату
			$temp     = Type_File_Utils::prepareFileForFormat($item, $node_url, $this->user_id);
			$output[] = Apiv2_Format::file($temp);
		}

		return $output;
	}

	/**
	 * Выбрасываем ошибку, если список файлов некорректный
	 *
	 * @param array $file_list
	 *
	 * @return void
	 * @throws ParamException
	 */
	protected function _throwIfFileListIsIncorrect(array $file_list):void {

		// если пришел пустой массив файлов
		if (count($file_list) < 1) {
			throw new ParamException("passed empty file_list");
		}

		// если пришел слишком большой массив
		if (count($file_list) > self::_MAX_FILES_COUNT) {
			throw new ParamException("passed file_list biggest than max");
		}
	}

	/**
	 * Преобразовать ключи в мапы
	 *
	 * @param array $file_list
	 *
	 * @return array
	 * @throws ParamException
	 */
	protected function _doDecryptFileKeyList(array $file_list):array {

		$file_map_list = [];
		foreach ($file_list as $item) {

			// преобразуем key в map
			$file_map = Type_Pack_File::tryDecrypt($item);

			// выбрасывем ошибку, если файл с другого dpc
			$this->_throwIfPassedFileMapFromAnotherServerType($file_map);

			$file_map_list[] = $file_map;
		}
		return $file_map_list;
	}

	/**
	 * Выбрасываем ошибку, если пришел файл с другого типа сервера
	 *
	 * @param string $file_map
	 *
	 * @return void
	 * @throws ParamException
	 */
	protected function _throwIfPassedFileMapFromAnotherServerType(string $file_map):void {

		// получаем dpc файла
		$server_type = Type_Pack_File::getServerType($file_map);

		// если dpc файла не совпал с текущим
		if ($server_type != CURRENT_SERVER) {
			throw new ParamException("The method is requested on a wrong server_type, " . __METHOD__);
		}
	}
}