<?php

namespace Compass\Conversation;

/**
 * класс для очистки форматирования из текста
 * регулярные выражения были позаимствованы из electron приложения для полной синхронизации (клиентские приложения, в свою очередь, синхронизированы между собой)
 *
 * @package Compass\Conversation
 */
class Domain_Search_Helper_FormattingCleaner {

	protected static float $_total_spent_time = 0;

	/**
	 * здесь храним список всех регулярных выражений с разметками форматирования
	 */
	protected const _REGEX_LIST = [
		self::_BOLD,
		self::_ITALIC,
		self::_STRIKE,
		self::_HIGHLIGHT_RED,
		self::_HIGHLIGHT_GREEN,
		self::_HIGHLIGHT_GRAY,
	];

	// разметка полужирного выделения
	protected const _BOLD = [
		"regex"       => "/(?<=^|[^a-zа-яё0-9]|[.?!)(,:<>])\*(?!\s)(([^\n]*?[^\n*\s]+?)(?!\n)+?)\*(?=$|[^a-zа-яё0-9]|[.?!)(,:<>])/s",
		"replacement" => "$2",
	];

	// разметка курсива
	protected const _ITALIC = [
		"regex"       => "/(?<=^|[^\wа-яё]|[.?!)(,:<>])_(?!\s)(([^\n]*?[^\n_\s]+?)(?!\n)+?)_(?=$|[^a-z0-9а-яё]|[.?!)(,:<>])/s",
		"replacement" => "$2",
	];

	// разметка зачеркивания
	protected const _STRIKE = [
		"regex"       => "/(?<=^|[^a-zа-яё0-9]|[.?!)(,:<>])~(?!\s)(([^\n]*?[^\n~\s]+?)(?!\n)+?)\~(?=$|[^a-zа-яё0-9]|[.?!)(,:<>])/s",
		"replacement" => "$2",
	];

	// разметка для красного маркера
	protected const _HIGHLIGHT_RED = [
		"regex"       => "/(?<=^|[^a-zа-яё0-9\/])\-{2}(.*?[^\n-].*?)\-{2}(?=$|[^a-zа-яё0-9\/\-]|[.?!)(,:<>])/s",
		"replacement" => "$1",
	];

	// разметка для зеленого маркера
	protected const _HIGHLIGHT_GREEN = [
		"regex"       => "/(?<=^|[^a-zа-яё0-9\/])\+{2}(.*?[^\n+].*?)\+{2}(?=$|[^a-zа-яё0-9\/\+]|[.?!)(,:<>])/s",
		"replacement" => "$1",
	];

	// разметка для серого форматирования
	protected const _HIGHLIGHT_GRAY = [
		"regex"       => "/(?<=^|[^a-zа-яё0-9\/])\`{2}(.*?[^\n`]+.*?)\`{2}(?=$|[^a-zа-яё0-9\/\`]|[.?!)(,:<>])/s",
		"replacement" => "$1",
	];

	/**
	 * лимит итераций очистки для одной регулярки – на случай когда очищаем текст а-ля "*привет *это *вложенное** форматирование*"
	 * совершим не больше итераций, чем указано в этой константе
	 */
	protected const _MAX_CLEAR_ITERATIONS = 20;

	/**
	 * Очищаем форматирование
	 *
	 * @return string
	 */
	public static function clear(string $text):string {

		$start_at = microtime(true);

		foreach (self::_REGEX_LIST as $regex) {

			// количество итераций очистки
			$i = 0;

			// количество замен
			$count = 0;

			// стираем разметку по регулярке, пока не избавимся от вложенных разметок
			// но не более 20 итераций для одной регулярки
			do {

				// стираем разметку форматирования
				$replacement = preg_replace($regex["regex"], $regex["replacement"], $text, count: $count);

				// если замена по регулярке вернула null, то перестаем обрабатывать ее
				if (is_null($replacement)) {
					break;
				}

				// запоминаем новый текст
				$text = $replacement;

				// считаем итерации, чтобы количество итераций было в рамках разумного
				$i++;
			} while ($count > 0 && $i <= self::_MAX_CLEAR_ITERATIONS);
		}

		// возвращаем ответ из полученных слов
		static::$_total_spent_time += microtime(true) - $start_at;
		return $text;
	}

	/**
	 * Записывает метрики времени исполнения.
	 */
	public static function writeMetrics():void {

		\BaseFrame\Monitor\Core::metric("cleaner_work_time_ms", (int) (static::$_total_spent_time * 1000))->seal();
		static::$_total_spent_time = 0;
	}
}