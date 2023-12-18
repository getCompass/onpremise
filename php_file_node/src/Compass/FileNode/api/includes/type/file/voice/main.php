<?php

namespace Compass\FileNode;

/**
 * Основной класс для работы с голосовыми
 */
class Type_File_Voice_Main extends Type_File_Main {

	// список доступных mime type
	public const ALLOWED_MIME_TYPE_LIST = [
		"audio/aac",
		"audio/x-hx-aac-adts",
	];

	// список расширений
	public const EXTENSION_LIST = [
		"aac",
	];
}