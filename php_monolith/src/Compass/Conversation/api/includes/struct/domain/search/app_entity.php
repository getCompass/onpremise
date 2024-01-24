<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Структура родителя совпадения.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_AppEntity {

	/**
	 * Родитель совпадения.
	 * Может быть как прямым, так и наследованным.
	 */
	public function __construct(
		public int    $entity_type,
		public string $entity_map,
	) {

		// nothing
	}
}
