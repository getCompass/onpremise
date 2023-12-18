<?php declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура с данными совпадения без форматирования.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_MessageTextReplacement {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public string $replacement,
		public Struct_Domain_Search_MessageTextReplacementData $data,
	) {

		// nothing
	}
}
