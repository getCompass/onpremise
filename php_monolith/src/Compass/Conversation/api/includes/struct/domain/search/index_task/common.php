<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Структура данных задачи базовой индексации.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_IndexTask_Common {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public string $entity_map,
		public array  $user_id_list,
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
