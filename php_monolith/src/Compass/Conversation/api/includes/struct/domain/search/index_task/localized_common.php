<?php declare(strict_types=1);

namespace Compass\Conversation;

use BaseFrame\System\Locale;

/**
 * Структура данных задачи базовой индексации.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_IndexTask_LocalizedCommon {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public string $entity_map,
		public array  $user_id_list,
		public string $locale = Locale::LOCALE_ENGLISH,
		public array  $extra = [],
	) {

		// nothing
	}

	/**
	 * Структура данных задачи базовой индексации.
	 * Конструктор первой версии.
	 */
	public static function fromRaw(array $raw):static {

		return new static(...$raw);
	}
}
