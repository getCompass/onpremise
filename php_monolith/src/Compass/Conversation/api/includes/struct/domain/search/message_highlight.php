<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Структура с данными подсветки совпадения.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_MessageHighlight {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public string $source,
		public string $replacement,
		public array  $replacement_list,
	) {

		// nothing
	}
}
