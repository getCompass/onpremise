<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Структура хранимых данных локации.
 * Уже восстановленная из записи поисковика.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_RawLocation {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public string $key,
		public int    $type,
		public int    $hit_count,
		public int    $last_hit_at,
		public array  $hit_list,
	) {

		// nothing
	}
}
