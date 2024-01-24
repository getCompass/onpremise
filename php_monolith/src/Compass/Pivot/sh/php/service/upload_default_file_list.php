<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

$default_file_list = Type_File_Default::getDefaultFileList();

// проверяем наличие и если нужно загружаем файл
foreach ($default_file_list as $default_file) {
	Type_File_Default::uploadFile($default_file["dictionary_key"], $default_file["file_name"], $default_file["file_source"], $default_file["file_hash"]);
}
