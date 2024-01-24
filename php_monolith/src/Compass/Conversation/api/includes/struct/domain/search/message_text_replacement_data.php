<?php declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура данных совпадения без форматирования.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_MessageTextReplacementData {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public string $replacement_text,
		public string $message_key,
	) {

		// nothing
	}
}
