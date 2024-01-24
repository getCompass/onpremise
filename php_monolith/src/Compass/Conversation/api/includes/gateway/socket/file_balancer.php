<?php

namespace Compass\Conversation;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Класс-интерфейс для работы с модулем company file_balancer
 */
class Gateway_Socket_FileBalancer extends Gateway_Socket_Default {

	// получаем информацию о файлах
	public static function getFileList(array $file_map_list):array {

		$ar_post = [
			"file_map_list" => $file_map_list,
		];
		[$_, $response] = self::doCall("files.getFileList", $ar_post);

		return $response["file_list"];
	}

	// получаем информацию о файлах
	public static function getFileWithContentList(array $file_map_list):array {

		$ar_post = [
			"file_map_list" => $file_map_list,
			"need_content"  => true
		];
		[$_, $response] = self::doCall("files.getFileWithContentList", $ar_post);

		return $response["file_with_content_list"];
	}

	// получаем подпись из массива параметров
	public static function doCall(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketCompanyUrl("file_balancer");
		return self::_doCall($url, $method, $params, $user_id);
	}

	/**
	 * Запрос для получения всех file_map.
	 * Нужен для пересоздания связей file-search_rel.
	 */
	#[ArrayShape([0 => "string[]", 1 => "bool"])]
	public static function getAllFileMaps(int $shard_id, int $table_id, int $limit, int $offset):array {

		$ar_post = [
			"shard_id" => $shard_id,
			"table_id" => $table_id,
			"limit"    => $limit,
			"offset"   => $offset,
		];

		[$_, $response] = self::doCall("files.getAllFileMaps", $ar_post);
		return [$response["file_map_list"], $response["has_next"]];
	}
}
