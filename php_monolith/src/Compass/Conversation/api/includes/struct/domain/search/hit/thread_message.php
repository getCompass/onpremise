<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Структура, описывающая совпадение тип «Сообщение-комментарий».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_Hit_ThreadMessage {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public array                         $item,
		public array                         $parent,
		public Struct_Domain_Search_HitExtra $extra,
		public int                           $updated_at,
		public int                           $previous_message_block,
		public int                           $next_message_block,
		public array                         $nested_location_list
	) {

		// nothing
	}
}
