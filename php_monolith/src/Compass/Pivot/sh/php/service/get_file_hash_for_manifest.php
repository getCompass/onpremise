<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

$default_file_list = Type_File_Default::getDefaultFileList();

// выводим в консоль хеши файлов из манифеста
foreach ($default_file_list as $default_file) {

	// получаем хеш файла
	$file_path = PATH_WWW . "default_file/" . $default_file["file_name"];
	$file_hash = Type_File_Utils::getFileHash($file_path);

	console("===================================================");
	console($default_file["dictionary_key"]);
	console($default_file["file_name"]);
	console($file_hash);
	console("===================================================");
}
