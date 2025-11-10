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
		"application/zstd",
		"application/gzip",
		"application/x-tar",
		"application/x-xz",
		"application/x-bzip",
		"application/x-bzip2",
		"application/x-brotli",
		"application/x-lz4",
		"application/x-lzip",
		"application/x-cpio",
	];

	// список расширений
	public const EXTENSION_LIST = [
		"zip",
		"rar",
		"7z",
		"tar",
		"gz",
		"xz",
		"bz",
		"bz2",
		"zst",
		"lzma",
		"cab",
		"br",
		"lz4",
		"lz",
		"cpio",
		"zipx",
		"tgz",
		"tbz",
		"tbz2",
		"txz",
		"tlz4"
	];
}