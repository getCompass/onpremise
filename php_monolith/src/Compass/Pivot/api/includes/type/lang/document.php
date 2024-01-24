<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с конфигом публичных документов
 */
class Type_Lang_Document extends Type_Lang_Default {

	// список файлов
	protected const _FILE_NAME = "public_documents.json";

	// локаль по умолчанию, если не указана ни одна
	public const DEFAULT_LANG = "en";

	// доступные локали
	protected const _ALLOW_LANG_LIST = [
		"ru",
		"en",
	];

	/**
	 * получаем список публичных документов
	 *
	 * @throws cs_PublicDocumentNotFound
	 * @throws \parseException|\cs_RowIsEmpty
	 */
	public static function getPublicDocuments(string $lang = self::DEFAULT_LANG, string $file_name = self::_FILE_NAME):array {

		// получаем локализированные файлы
		$public_documents = self::_getConfig($lang, $file_name);

		if (!$public_documents || count($public_documents) == 0) {

			// если был передан не корректный язык, нужно все равно отдать на дефолтном
			$public_documents = self::_getConfig(self::DEFAULT_LANG, $file_name);

			// если и на дефолтном нет, выкидываем ошибку
			if (!$public_documents) {
				throw new cs_PublicDocumentNotFound();
			}
		}

		// оставляем только нужные документы по имени приложения
		$app_name                   = Type_Api_Platform::getAppNameByUserAgent(getUa());
		$app_name                   = mb_substr($app_name, 0, 7);
		$formatted_public_documents = [];
		foreach ($public_documents as $document) {

			if ($document["app_name"] == $app_name) {
				$formatted_public_documents[] = $document;
			}
		}

		$documents_list = [];
		foreach ($formatted_public_documents as $document) {
			$documents_list[] = self::_formatPublicDocument($document);
		}

		return $documents_list;
	}

	/**
	 * проверяем, зарегистрирован ли номер
	 *
	 */
	public static function isCorrectLang(string $lang):bool {

		if (mb_strlen($lang) != 2) {
			return false;
		}

		if (!in_array($lang, self::_ALLOW_LANG_LIST)) {
			return false;
		}

		return true;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * форматируем сущность document
	 *
	 * @throws \parseException|\cs_RowIsEmpty
	 */
	protected static function _formatPublicDocument(array $document):Struct_Config_Lang_Document {

		return match ($document["version"]) {

			1       => self::_formatV1($document),

			default => throw new ParseFatalException("try get unknown version"),
		};
	}

	/**
	 * форматируем документа первой версией
	 *
	 * @throws \cs_RowIsEmpty
	 */
	protected static function _formatV1(array $public_document):Struct_Config_Lang_Document {

		$file = Gateway_Db_PivotSystem_DefaultFileList::get($public_document["dictionary_name"]);

		return new Struct_Config_Lang_Document(
			$public_document["name"],
			$public_document["title"],
			$public_document["description"],
			$public_document["file_url"],
			$file->file_key,
			$public_document["version"],
		);
	}
}