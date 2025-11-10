<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

$custom_file_list = Type_File_Custom::getCustomFileList();

// проверяем наличие и если нужно загружаем файл
foreach ($custom_file_list as $custom_file) {

	Type_File_Custom::uploadFile(
		$custom_file["dictionary_key"], $custom_file["file_name"], $custom_file["file_source"], $custom_file["file_hash"]
	);
}
