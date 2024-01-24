<?php

namespace Compass\Conversation;

use BaseFrame\System\Locale;

/**
 * Расширение для класса-обработчика сообщений.
 * Описывает поведение сообщений как сущностей для поиска.
 *
 * Основная задача — формировать данные для индексации
 * непосредственно самих сообщений (тело и содержимое вложенных сообщений)
 */
trait Type_Conversation_Message_Handler_Indexation {

	/**
	 * Определяет, может ли сообщение как либо участвовать в поиске.
	 * Сообщения, которые могут быть задействованы, получают свой уникальный search_id.
	 */
	public static function isSearchable(array $message):bool {

		return in_array($message["type"], static::_INDEXABLE_TYPE_LIST);
	}

	/**
	 * Определяет, может ли сообщение быть проиндексировано.
	 * Сообщений для индексации будут добавлены в таблицу поиска как искомые сущности.
	 */
	public static function isIndexable(array $message):bool {

		return in_array($message["type"], static::_INDEXABLE_TYPE_LIST);
	}

	/**
	 * Конвертирует сообщение в структуру для добавления в индекс.
	 */
	public static function prepareIndexText(array $message, string $locale):array {

		$body_text   = static::_getBodyText($message);
		$nested_text = static::_getNestedText($message);

		// выпиливаем эмодзи
		$body_text   = removeEmojiFromText($body_text);
		$nested_text = removeEmojiFromText($nested_text);

		// выпиливаем упоминания
		$body_text   = Domain_Search_Helper_MessageText::replaceMentions($body_text);
		$nested_text = Domain_Search_Helper_MessageText::replaceMentions($nested_text);

		return [
			Domain_Search_Helper_Stemmer::stemText($body_text, [$locale, Locale::LOCALE_ENGLISH]),
			Domain_Search_Helper_Stemmer::stemText($nested_text, [$locale, Locale::LOCALE_ENGLISH]),
		];
	}

	/**
	 * Возвращает тело сообщения для индексации.
	 */
	protected static function _getBodyText(array $message):string {

		return Type_Conversation_Message_Main::getHandler($message)::getText($message);
	}

	/**
	 * Возвращает текст вложенных сообщений для индексации.
	 */
	protected static function _getNestedText(array $message):string {

		if (static::hasNestedMessages($message)) {
			return implode("\n", static::_resolveNestedTexts($message));
		}

		return "";
	}

	/**
	 * Возвращает массив с текстами вложенных сообщений.
	 */
	protected static function _resolveNestedTexts(array $message):array {

		$output = [];

		foreach (Type_Conversation_Message_Main::getHandler($message)::getRepostedOrQuotedMessageList($message) as $nested_message) {

			$handler = Type_Conversation_Message_Main::getHandler($nested_message);

			// сразу сохраняем текст самого сообщения
			$output[] = $handler::getText($nested_message);

			// репосты и цитаты разбираем рекурсивно
			if (static::hasNestedMessages($nested_message)) {
				array_push($output, ...static::_resolveNestedTexts($nested_message));
			}
		}

		return $output;
	}

	/**
	 * Конвертирует сообщение в структуру для добавления в индекс.
	 */
	public static function prepareFiles(array $message):array {

		return [
			static::_getBodyFile($message),
			static::_getNestedFiles($message),
		];
	}

	/**
	 * Возвращает файл самого сообщения.
	 */
	protected static function _getBodyFile(array $message):string|false {

		if (Type_Conversation_Message_Main::getHandler($message)::isAnyFile($message)) {

			// получаем файл-мап из сообщения
			$file_map = Type_Conversation_Message_Main::getHandler($message)::getFileMap($message);

			// если файл индексируется, то возвращаем его данные
			if (in_array(\CompassApp\Pack\File::getFileType($file_map), static::_INDEXABLE_FILE_TYPE_LIST)) {
				return $file_map;
			}
		}

		return false;
	}

	/**
	 * Возвращает массив с вложенными файлами.
	 */
	protected static function _getNestedFiles(array $message):array {

		if (!static::hasNestedMessages($message)) {
			return [];
		}

		return arrayFlat(static::_resolveNestedFiles($message));
	}

	/**
	 * Возвращает данные связанных файлов.
	 */
	protected static function _resolveNestedFiles(array $message):array {

		$output = [];

		foreach (Type_Conversation_Message_Main::getHandler($message)::getRepostedOrQuotedMessageList($message) as $nested_message) {

			$handler = Type_Conversation_Message_Main::getHandler($nested_message);

			if ($handler::isAnyFile($nested_message)) {

				// получаем файл-мап из сообщения
				$file_map = Type_Conversation_Message_Main::getHandler($nested_message)::getFileMap($nested_message);

				// тип файла объявлен как неиндексируемый, то пропускаем
				if (!in_array(\CompassApp\Pack\File::getFileType($file_map), static::_INDEXABLE_FILE_TYPE_LIST)) {
					continue;
				}

				$output[] = $file_map;
			}

			// перебираем вложенные сообщения
			if (static::hasNestedMessages($nested_message)) {
				array_push($output, ...static::_resolveNestedFiles($nested_message));
			}
		}

		return $output;
	}

	/**
	 * Проверяет, принадлежит файл сообщению
	 */
	public static function isFileBelongsToMessage(array $file, array $message):bool {

		// если сообщение не содержит файла, пропускаем
		if (!static::isAnyFile($message)) {
			return false;
		}

		// если сообщение не содержит этот файл, пропускаем
		return static::getFileMap($message) === $file["file_map"];
	}

	/**
	 * Проверяет, принадлежит превью сообщению
	 */
	public static function isPreviewBelongsToMessage(array $preview, array $message):bool {

		if (!isset($message["preview_map"])) {
			return false;
		}

		return Type_Conversation_Message_Main::getHandler($message)::getPreview($message) === $preview["preview_map"];
	}

	/**
	 * Возвращает список процитированных/репостнутных сообщений одним массивом.
	 */
	public static function getFlatNestedMessageList(array $message):array {

		$output = [];

		if (!static::hasNestedMessages($message)) {
			return [];
		}

		foreach (Type_Conversation_Message_Main::getHandler($message)::getRepostedOrQuotedMessageList($message) as $nested_message) {

			$output[] = $nested_message;

			if (static::hasNestedMessages($nested_message)) {
				array_push($output, ...static::getFlatNestedMessageList($nested_message));
			}
		}

		return $output;
	}

	/**
	 * является ли сообщение каким-либо файлом?
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function isAnyFile(array $message):bool {

		return Type_Conversation_Message_Main::getHandler($message)::isAnyFile($message);
	}

	/**
	 * получаем file_map из сообщения
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getFileMap(array $message):string {

		return Type_Conversation_Message_Main::getHandler($message)::getFileMap($message);
	}

	/**
	 * Проверяет, содержит ли сообщение вложенные сообщения.
	 */
	public static function hasNestedMessages(array $message):bool {

		$handler = Type_Conversation_Message_Main::getHandler($message);
		return $handler::isRepost($message)
			|| $handler::isQuote($message)
			|| $handler::isQuoteFromThread($message)
			|| $handler::isSystemBotRemind($message)
			|| $handler::isSystemBotRemindFromThread($message);
	}
}
