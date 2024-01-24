<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Структура для вложенной локации результата поиска.
 * @property Struct_Domain_Search_HitRow[] $nested_hit_row_list
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_HitRowNestedLocation {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public Struct_Domain_Search_HitRow $hit_row,
		public array                       $nested_hit_row_list,
		public int                         $total_hit_count,
	) {

		// nothing
	}
}
