<?php

namespace Compass\FileNode;

/**
 * основной класс для работы с документами
 */
class Type_File_Document_Main extends Type_File_Main {

	// список доступных mime type
	public const ALLOWED_MIME_TYPE_LIST = [
		"application/pdf",
		"application/msword",
		"application/rtf",
		"application/excel",
		"application/vnd.ms-excel",
		"application/vnd.ms-powerpoint",
		"application/vnd.oasis.opendocument.text",
		"application/vnd.oasis.opendocument.spreadsheet",
		"application/vnd.openxmlformats-officedocument.wordprocessingml.document",
		"text/plain",
		"text/html",
		"text/css",
		"text/richtext",
		"text/rtf",
		"text/vcard",
		"text/calendar",
		"application/javascript",
		"application/json",
		"application/xml",
		"application/cmd",
		"text/csv",
		"text/javascript",
		"text/php",
		"text/xml",
		"text/markdown",
		"cache-manifest",
		"application/vnd.openxmlformats-officedocument.wordprocessingml.document",
		"application/vnd.openxmlformats-officedocument.wordprocessingml.documentapplication/vnd.openxmlformats-officedocument.wordprocessingml.document",
		"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
		"application/vnd.openxmlformats-officedocument.presentationml.presentation",
		"application/vnd.openxmlformats-officedocument.wordprocessingml.template",
		"application/vnd.openxmlformats-officedocument.spreadsheetml.template",
		"application/vnd.ms-excel.addin.macroEnabled.12",
		"application/vnd.ms-excel.sheet.binary.macroEnabled.12",
		"application/vnd.openxmlformats-officedocument.presentationml.template",
		"application/vnd.openxmlformats-officedocument.presentationml.slideshow",
		"application/x-empty",
	];

	// список расширений
	public const EXTENSION_LIST = [
		"pdf",
		"dotx",
		"docm",
		"docx",
		"doc",
		"dot",
		"word",
		"rtf",
		"xl",
		"xls",
		"xlsx",
		"xltx",
		"xlam",
		"xlsb",
		"ppt",
		"pptx",
		"pot",
		"pps",
		"ppa",
		"odt",
		"plain",
		"html",
		"css",
		"js",
		"json",
		"xml",
		"cmd",
		"csv",
		"php",
		"md",
		"htm",
		"shtml",
		"log",
		"text",
		"txt",
		"rtx",
		"rtf",
		"vcf",
		"vcard",
		"ics",
		"xml",
		"xsl",
	];

	/**
	 * Список расширений, которые индексируются для функционала поиска
	 */
	public const INDEXABLE_EXTENSION_LIST = [
		"doc",
		"docx",
		"xls",
		"xlsx",
		"pdf",
	];

	/**
	 * Проверяем, является ли документ индексируемым
	 *
	 * @return bool
	 */
	public static function isIndexableDocument(string $file_extension):bool {

		return in_array($file_extension, self::INDEXABLE_EXTENSION_LIST);
	}
}