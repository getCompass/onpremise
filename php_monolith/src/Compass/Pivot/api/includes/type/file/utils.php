<?php

namespace Compass\Pivot;

/**
 * Вспомогательные функции
 */
class Type_File_Utils {

	// получаем хэш файла
	public static function getFileHash(string $file_path):string {

		return sha1_file($file_path);
	}
}