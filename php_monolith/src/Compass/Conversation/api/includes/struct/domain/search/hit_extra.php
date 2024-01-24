<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Структура экстра-данных совпадения.
 * @property Struct_Domain_Search_SpotDetail[] $spot_detail_list
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_HitExtra {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public array $spot_list,
		public array $spot_detail_list,
		public array $message_text_replacement_list,
	) {

		// nothing
	}
}
