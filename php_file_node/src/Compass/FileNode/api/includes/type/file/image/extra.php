<?php

namespace Compass\FileNode;

/**
 * основной класс для работы с картинками
 */
class Type_File_Image_Extra {

	/*
	* Структура extra:
	* Array
	* (
	*     [version] => 1
	*     [original_part_path] => gj/x4/Kf/S6/qT/CkhmwUq4VWZJbn7iaRz3PPnnU8CMe3GRoYeUSFYNBYxMlE4vwEzluXIp6lbloScM.jpg
	*     [original_image_item] => Array
	*         (
	*             [part_path] => gj/x4/Kf/S6/qT/CkhmwUq4VWZJbn7iaRz3PPnnU8CMe3GRoYeUSFYNBYxMlE4vwEzluXIp6lbloScM.jpg
	*             [width] => 720
	*             [height] => 720
	*             [size_kb] => 1280
	*         )
	*
	*     [image_size_list] => Array
	*         (
	*             [0] => Array
	*                 (
	*                     [part_path] => gj/x4/Kf/S6/qT/CkhmwUq4VWZJbn7iaRz3PPnnU8CMe3GRoYeUSFYNBYxMlE4vwEzluXIp6lbloScM_w360.jpg
	*                     [width] => 360
	*                     [height] => 360
	*                     [size_kb] => 590
	*                 )
	*
	*         )
	* )
	*
	*/

	// текущая версия поля extra
	protected const _EXTRA_VERSION = 1;

	// формирует extra нужной структуры
	public static function getExtra(string $original_part_path, int $company_id, string $company_url, array $original_image_item, array $image_size_list, string $parent_file_key, array $extra = []):array {

		$extra["version"]             = self::_EXTRA_VERSION;
		$extra["original_part_path"]  = $original_part_path;
		$extra["original_image_item"] = $original_image_item;
		$extra["image_size_list"]     = $image_size_list;
		$extra["company_id"]          = $company_id;
		$extra["company_url"]         = $company_url;

		if (mb_strlen($parent_file_key) > 0) {
			$extra["parent_file_key"] = $parent_file_key;
		}

		return $extra;
	}

	// получаем image_size_list из extra
	public static function getImageSizeListFromExtra(array $extra):array {

		if (!isset($extra["image_size_list"])) {
			return [];
		}

		return $extra["image_size_list"];
	}

	// получаем preview_original_part_path из extra
	public static function getImageOriginalPartPath(array $extra):string {

		if (!isset($extra["original_part_path"])) {
			return "";
		}

		return $extra["original_part_path"];
	}
}