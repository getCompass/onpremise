<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Структура данных задачи базовой индексации.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_IndexTask_Entity {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public string $entity_map,
		public array  $data = [],
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
