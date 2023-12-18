<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Структура подготовленной для обновления ранее индексированной сущности.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_Replace {

	/**
	 * Структура подготовленной для обновления ранее индексированной сущности.
	 *
	 * @param int    $search_id уникальный идентификатор для поиска
	 * @param int    $updated_at дата актуализации сущности
	 * @param string $field1 текстовое поле для индексации
	 * @param string $field2 текстовое поле для индексации
	 * @param string $field3 текстовое поле для индексации
	 * @param string $field4 текстовое поле для индексации
	 */
	public function __construct(
		public int    $search_id,
		public int    $updated_at,
		public string $field1,
		public string $field2 = "",
		public string $field3 = "",
		public string $field4 = "",
	) {

	}
}