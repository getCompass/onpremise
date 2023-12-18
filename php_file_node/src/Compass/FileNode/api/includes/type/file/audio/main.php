<?php

namespace Compass\FileNode;

/**
 * Основной класс для работы с аудио
 */
class Type_File_Audio_Main extends Type_File_Main {
	public const CONVERT_TYPE = "mp3";

	// список доступных mime type
	public const ALLOWED_MIME_TYPE_LIST = [
		"application/octet-stream",
		"audio/mp4",
		"audio/mpeg",
		"audio/midi",
		"audio/webm",
		"audio/ogg",
		"audio/basic",
		"audio/L24",
		"audio/vorbis",
		"audio/x-ms-wma",
		"audio/x-ms-wax",
		"audio/vnd.rn-realaudio",
		"audio/vnd.wave",
		"audio/mp3",
		"audio/x-m4a",
		"audio/x-wav",
		"audio/x-flac",
		"audio/flac",
		"audio/aac",
		"audio/x-aiff",
		"audio/aiff",
		"audio/x-m4a",
		"audio/amr",
		"video/x-ms-asf",
		"video/mp4",
		"video/x-matroska",
	];

	// список типов для конвертации
	public const EXTENSION_NEED_CONVERT_LIST = [
		"aac",
		"amr",
		"aiff",
		"wma",
		"m4r",
		"ogg",
		"mkv",
		"wav",
		"m4a",
	];

	// список расширений
	public const EXTENSION_LIST = [
		"midi",
		"webm",
		"ogg",
		"mp4",
		"wma",
		"wax",
		"mp3",
		"flac",
		"wav",
		"aac",
		"amr",
		"aif",
		"aiff",
		"m4r",
		"m4a",
		"mkv",
	];
}