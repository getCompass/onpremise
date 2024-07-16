<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс для получения ссылок для списка файлов
 */
class Domain_File_Action_GetLinkForAvatarList {

	/** @var int минимальный оптимальный размер аватара */
	protected const _MIN_OPTIMAL_WIDTH = 300;

	/**
	 * Выполняем действие
	 *
	 * @param array $avatar_file_map_list
	 *
	 * @return array
	 * @throws ReturnFatalException
	 * @throws ParseFatalException
	 */
	public static function do(array $avatar_file_map_list):array {

		// убираем все пустые строки
		$avatar_file_map_list = array_filter($avatar_file_map_list);

		// если тут пустота, то ничего не делаем
		if (count($avatar_file_map_list) < 1) {
			return [];
		}

		// отправляем socket-запрос
		$file_list = Gateway_Socket_PivotFileBalancer::getFileList($avatar_file_map_list);

		// пробегаемся по каждому файлу и собираем список ссылоку на каждый из file_map
		$avatar_link_by_file_map = [];
		foreach ($file_list as $file) {

			// берем ссылку на оптимальный по размеру аватар
			$image_version_list                         = $file["data"]["cropped_image_version_list"] ?? $file["data"]["image_version_list"];
			$avatar_link_by_file_map[$file["file_map"]] = self::_chooseOptimalImageVersion($file["url"], $image_version_list);
		}

		return $avatar_link_by_file_map;
	}

	/**
	 * Выбираем оптимальный по размеру файл
	 *
	 * @param array $image_version_list
	 *
	 * @return string
	 */
	protected static function _chooseOptimalImageVersion(string $original_image_url, array $image_version_list):string {

		// убираем все изображения, где размер меньше минимального оптимального
		$image_version_list = array_filter($image_version_list,  static fn (array $image_version) => $image_version["width"] >= self::_MIN_OPTIMAL_WIDTH);

		// если после фильтрации не осталось изображений, то используем оригинальный размер изображения
		if (count($image_version_list) === 0) {
			return $original_image_url;
		}

		// сортируем по размеру по возрастанию
		usort($image_version_list, function(array $a, array $b) {

			return $a["width"] <=> $b["width"];
		});

		// возвращаем второй размер с начала (или первый, если только один размер)
		return $image_version_list[1]["url"] ?? $image_version_list[0]["url"];
	}
}