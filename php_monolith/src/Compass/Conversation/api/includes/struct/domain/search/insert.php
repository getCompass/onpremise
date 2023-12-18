<?php declare(strict_types=1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Структура подготовленной для индексации сущности.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_Insert {

	/**
	 * Структура подготовленной для индексации сущности.
	 *
	 * @param int    $user_id пользователь, для которого индексируем запись
	 * @param int    $search_id уникальный идентификатор для поиска
	 * @param int    $creator_id пользователь, которому принадлежит исходная сущность
	 * @param int    $type тип сущности
	 * @param int    $updated_at дата актуализации сущности
	 * @param int    $parent_id search_id для родительской сущности
	 * @param array  $inherit_parent_id_list наследованный список родителей
	 * @param int    $parent_type_mask типы всех известных родителей
	 * @param int    $group_by_conversation_parent_id родитель для группировки по диалогам
	 * @param string $field1 текстовое поле для индексации
	 * @param string $field2 текстовое поле для индексации
	 * @param string $field3 текстовое поле для индексации
	 * @param string $field4 текстовое поле для индексации
	 */
	public function __construct(
		public int    $user_id,
		public int    $creator_id,
		public int    $search_id,
		public int    $type,
		public int    $attribute_mask,
		public int    $updated_at,
		public int    $parent_id,
		public array  $inherit_parent_id_list,
		public int    $parent_type_mask,
		public int    $group_by_conversation_parent_id,
		public string $field1,
		public string $field2 = "",
		public string $field3 = "",
		public string $field4 = "",
	) {

		// проверяем, что значения массива с идентификаторами родителей корректные
		foreach ($inherit_parent_id_list as $id) {

			if (!is_int($id)) {
				throw new ReturnFatalException("only int values allowed");
			}
		}
	}
}