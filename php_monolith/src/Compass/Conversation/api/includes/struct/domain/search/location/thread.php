<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Структура, описывающая локацию поиска «Тред».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_Location_Thread {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public array $thread_meta,
		public int   $hit_count,
		public array $hit_list = []
	) {

		// nothing
	}
}
