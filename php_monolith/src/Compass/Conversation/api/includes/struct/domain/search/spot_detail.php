<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Структура с данными совпадения.
 * Описывает в каком атрибуте было совпадение и правила подсветки.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_SpotDetail {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public string                                 $field,
		public ?array                                 $field_extra,
		public ?Struct_Domain_Search_MessageHighlight $highlight_info,
	) {

		// nothing
	}
}
