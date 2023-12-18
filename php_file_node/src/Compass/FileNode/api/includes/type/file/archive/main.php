<?php

namespace Compass\FileNode;

/**
 * Основной класс для работы с архивами
 */
class Type_File_Archive_Main extends Type_File_Main {

	// список доступных mime type
	public const ALLOWED_MIME_TYPE_LIST = [
		"application/zip",
		"application/x-rar",
		"application/x-rar-compressed",
		"application/x-msdownload",
		"application/vnd.ms-cab-compressed",
		"application/x-7z-compressed",
	];

	// список расширений
	public const EXTENSION_LIST = [
		"zip",
		"rar",
		"7z",
		"tar",
		"gz",
	];
}