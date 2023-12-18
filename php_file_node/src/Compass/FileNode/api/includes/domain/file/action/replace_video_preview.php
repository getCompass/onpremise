<?php

namespace Compass\FileNode;

/**
 * Класс описывает действие по замене превью изображений (превью оригинального размера + все возможные размеры) у видео
 */
class Domain_File_Action_ReplaceVideoPreview {

	/**
	 * Выполняем действие
	 *
	 * @param string $video_file_key   ключ файла, в котором будем заменять превью
	 * @param string $preview_file_key ключ превью изображения
	 *
	 * @throws Domain_File_Exception_FileNotFound
	 */
	public static function do(string $video_file_key, string $preview_file_key):void {

		// получаем запись видео
		$video_file_row = Gateway_Db_FileNode_File::getOne($video_file_key);

		// получаем запись заменяемой картинки превью
		$replace_preview_file_row = Gateway_Db_FileNode_File::getOne($preview_file_key);

		// если нечего заменять или нечем заменять
		if (count($video_file_row) < 1 || count($replace_preview_file_row) < 1) {
			throw new Domain_File_Exception_FileNotFound();
		}

		// получаем обе нарезки
		$video_preview_size_list   = Type_File_Video_Extra::getPreviewSizeList($video_file_row["extra"]);
		$replace_preview_size_list = Type_File_Image_Extra::getImageSizeListFromExtra($replace_preview_file_row["extra"]);

		// проверяем что есть нарезки у превьюшки, которую будем подставлять
		if (count($replace_preview_size_list) < 1) {
			throw new Domain_File_Exception_FileNotFound();
		}

		// преобразуем replace_preview_size_list в структуру [ [$width] => $replace_preview_size_item ], чтобы по размеру легко получить нужную структуру
		$replace_preview_size_list_map_by_width = array_column($replace_preview_size_list, null, "width");

		// преобразуем video_preview_size_list в структуру [ [$width] => $welcome_video_preview_size_item ], чтобы по размеру легко получить нужную структуру
		$video_preview_size_map_by_width = array_column($video_preview_size_list, null, "width");

		// актуализируем старые превьюшки разных размеров новыми
		$video_preview_size_map_by_width = self::_actualizeVideoPreviewSizeList($video_preview_size_map_by_width, $replace_preview_size_list_map_by_width);

		// заменяем превью оригинала
		$video_file_row = self::_replacePreviewOriginal($video_file_row, $replace_preview_file_row);

		// обновляем extra записи
		self::_updateExtra($video_file_row, $video_preview_size_map_by_width);
	}

	/**
	 * Актуализируем старые превьюшки новыми:
	 * – Физически (на диске) заменяем старые превьюшки новыми
	 * – Удаляем старые превьюшки (физически не удаляем с диска, чтобы не было 404 http status ошибок у клиента)
	 * – Добавляем новые превьюшки
	 * – Актуализируем extra, но не обновляем в базе данных
	 *
	 * @return array
	 */
	protected static function _actualizeVideoPreviewSizeList(array $video_preview_size_map_by_width, array $replace_preview_size_list_map_by_width):array {

		// пробегаемся по старым превьюшкам
		foreach ($video_preview_size_map_by_width as $width => $video_preview_size_item) {

			// если такого размера нет в новой превьюшке, то удаляем его
			if (!isset($replace_preview_size_list_map_by_width[$width])) {

				unset($video_preview_size_map_by_width[$width]);
				continue;
			}

			// иначе заменяем превьюшку:

			// получаем новую превьюшку
			$new_preview_size_item = $replace_preview_size_list_map_by_width[$width];

			// актуализируем extra:
			// - меняем height, size_kb, part_path
			// - width остается неизменным
			$video_preview_size_item["height"]    = $new_preview_size_item["height"];
			$video_preview_size_item["size_kb"]   = $new_preview_size_item["size_kb"];
			$video_preview_size_item["part_path"] = $new_preview_size_item["part_path"];

			$video_preview_size_map_by_width[$width] = $video_preview_size_item;
		}

		// проходимся по новым превьюшкам и добавляем новые появившиеся размеры
		foreach ($replace_preview_size_list_map_by_width as $width => $new_preview_size_item) {

			// если такой размер уже есть в превьюшках видео, то скипаем
			if (isset($video_preview_size_map_by_width[$width])) {
				continue;
			}

			// иначе добавляем
			$video_preview_size_map_by_width[$width] = $new_preview_size_item;
		}

		// возвращаем актуализированные размеры видео
		return $video_preview_size_map_by_width;
	}

	/**
	 * заменяем превью оригинала
	 *
	 * @param array $video_file_row
	 * @param array $replace_preview_file_row
	 *
	 * @return array
	 * @throws Domain_File_Exception_FileNotFound
	 */
	protected static function _replacePreviewOriginal(array $video_file_row, array $replace_preview_file_row):array {

		// получаем пути до оригиналов превью (проверяем что они есть)
		$video_preview_original_part_path   = Type_File_Video_Extra::getPreviewOriginalPartPath($video_file_row["extra"]);
		$replace_preview_original_part_path = Type_File_Image_Extra::getImageOriginalPartPath($replace_preview_file_row["extra"]);
		if (mb_strlen($video_preview_original_part_path) < 1 || mb_strlen($replace_preview_original_part_path) < 1) {
			throw new Domain_File_Exception_FileNotFound();
		}

		// заменяем оригинал
		$video_file_row["extra"] = Type_File_Video_Extra::setPreviewOriginalPartPath($video_file_row["extra"], $replace_preview_original_part_path);

		return $video_file_row;
	}

	/**
	 * Обновляем extra в базе данных и в балансере
	 */
	protected static function _updateExtra(array $video_file_row, array $actual_video_preview_size_map_by_width):void {

		// избавляемся от ключей в мапе – превращаем ее в слайс
		$actual_video_preview_size_list = array_values($actual_video_preview_size_map_by_width);

		// складываем в extra обновленную информацию по нарезанным превьюшкам
		$video_file_row["extra"] = Type_File_Video_Extra::setPreviewSizeList($video_file_row["extra"], $actual_video_preview_size_list);

		// обновляем запись в БД и в балансировщике
		Type_File_Main::updateFile($video_file_row["file_key"], $video_file_row["extra"]);
	}
}