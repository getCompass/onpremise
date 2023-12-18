<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Структура хранения данных вложенной локации в совпадении.
 * @property Struct_Domain_Search_RawHit[] $nested_hit_list
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_RawHitNestedLocation {

	/**
	 * Структура хранения данных вложенной локации в совпадении.
	 */
	public function __construct(
		public Struct_Domain_Search_RawHit $location,
		public array                       $nested_hit_list,
		public int                         $hit_count = 0,
		public int                         $last_hit_at = 0,
	) {

		// nothing
	}
}
