<?php

namespace Compass\FileNode;

/**
 * Основной класс для работы с картинками
 */
class Type_File_Image_Main extends Type_File_Main {

	// список доступных mime type
	public const ALLOWED_MIME_TYPE_LIST = [
		"image/png",
		"image/jpeg",
		"image/jpg",
		"image/x-icon",
		"image/bmp",
		"image/x-ms-bmp",
		"image/vnd.microsoft.icon",
		"image/heif",
		"image/webp",
	];

	// список расширений
	public const EXTENSION_LIST = [
		"png",
		"jpeg",
		"bmp",
		"jpg",
		"ico",
	];
}