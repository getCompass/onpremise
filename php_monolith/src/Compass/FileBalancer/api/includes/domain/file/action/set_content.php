<?php

namespace Compass\FileBalancer;

/**
 * Класс, описывающий действие обновления содержимого файла
 */
class Domain_File_Action_SetContent {

	/**
	 * выполняем действие
	 */
	public static function do(string $file_map, string $content):void {

		// формируем массив для обновления
		$set = [
			"updated_at" => time(),
			"content"    => $content,
		];

		// обновляем запись с файлом
		Type_File_Main::set($file_map, $set);

		// добавляем в поиск, если этот файл может быть найден
		if (static::_isSearchable($file_map)) {
			static::_updateSpaceFileSearchData($file_map);
		}
	}

	/**
	 * Проверяет, является ли файл файлом пространства
	 */
	protected static function _isSearchable(string $file_map):bool {

		return CURRENT_SERVER == CLOUD_SERVER && Type_Pack_File::getCompanyId($file_map) !== 0;
	}

	/**
	 * Запускает обновление поисковых данных файла.
	 */
	protected static function _updateSpaceFileSearchData(string $file_map):void {

		try {

			// пытаемся зарядить задачу на переиндексацию файла
			Gateway_Socket_Conversation::reindexFile($file_map);
		} catch (\Exception) {
			// если не удалось передать на индексацию, то ничего страшного
		}
	}
}